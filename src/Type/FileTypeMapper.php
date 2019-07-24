<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeMap;

class FileTypeMapper
{

	private const SKIP_NODE = 1;
	private const POP_TYPE_MAP_STACK = 2;

	/** @var \PHPStan\Parser\Parser */
	private $phpParser;

	/** @var \PHPStan\PhpDoc\PhpDocStringResolver */
	private $phpDocStringResolver;

	/** @var \PHPStan\Cache\Cache */
	private $cache;

	/** @var \PHPStan\Broker\AnonymousClassNameHelper */
	private $anonymousClassNameHelper;

	/** @var \PHPStan\PhpDoc\TypeNodeResolver */
	private $typeNodeResolver;

	/** @var \PHPStan\PhpDoc\ResolvedPhpDocBlock[][] */
	private $memoryCache = [];

	/** @var (false|callable|\PHPStan\PhpDoc\ResolvedPhpDocBlock)[][] */
	private $inProcess = [];

	public function __construct(
		Parser $phpParser,
		PhpDocStringResolver $phpDocStringResolver,
		Cache $cache,
		AnonymousClassNameHelper $anonymousClassNameHelper,
		TypeNodeResolver $typeNodeResolver
	)
	{
		$this->phpParser = $phpParser;
		$this->phpDocStringResolver = $phpDocStringResolver;
		$this->cache = $cache;
		$this->anonymousClassNameHelper = $anonymousClassNameHelper;
		$this->typeNodeResolver = $typeNodeResolver;
	}

	public function getResolvedPhpDoc(
		string $fileName,
		?string $className,
		?string $traitName,
		string $docComment
	): ResolvedPhpDocBlock
	{
		if ($className === null && $traitName !== null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$phpDocKey = $this->getPhpDocKey($className, $traitName, $docComment);
		$phpDocMap = [];

		if (!isset($this->inProcess[$fileName])) {
			$phpDocMap = $this->getResolvedPhpDocMap($fileName);
		}

		if (isset($phpDocMap[$phpDocKey])) {
			return $phpDocMap[$phpDocKey];
		}

		if (!isset($this->inProcess[$fileName][$phpDocKey])) { // wrong $fileName due to traits
			return ResolvedPhpDocBlock::createEmpty();
		}

		if ($this->inProcess[$fileName][$phpDocKey] === false) { // PHPDoc has cyclic dependency
			return ResolvedPhpDocBlock::createEmpty();
		}

		if (is_callable($this->inProcess[$fileName][$phpDocKey])) {
			$resolveCallback = $this->inProcess[$fileName][$phpDocKey];
			$this->inProcess[$fileName][$phpDocKey] = false;
			$this->inProcess[$fileName][$phpDocKey] = $resolveCallback();
		}

		assert($this->inProcess[$fileName][$phpDocKey] instanceof ResolvedPhpDocBlock);
		return $this->inProcess[$fileName][$phpDocKey];
	}

	/**
	 * @param string $fileName
	 * @return \PHPStan\PhpDoc\ResolvedPhpDocBlock[]
	 */
	private function getResolvedPhpDocMap(string $fileName): array
	{
		if (!isset($this->memoryCache[$fileName])) {
			$modifiedTime = filemtime($fileName);
			if ($modifiedTime === false) {
				$modifiedTime = time();
			}
			$cacheKey = sprintf('%s-%d-%s', $fileName, $modifiedTime, $this->typeNodeResolver->getCacheKey());
			$map = $this->cache->load($cacheKey);

			if ($map === null) {
				$map = $this->createResolvedPhpDocMap($fileName);
				$this->cache->save($cacheKey, $map);
			}

			$this->memoryCache[$fileName] = $map;
		}

		return $this->memoryCache[$fileName];
	}

	/**
	 * @param string $fileName
	 * @return \PHPStan\PhpDoc\ResolvedPhpDocBlock[]
	 */
	private function createResolvedPhpDocMap(string $fileName): array
	{
		$phpDocMap = $this->createFilePhpDocMap($fileName, null, null);

		try {
			$this->inProcess[$fileName] = $phpDocMap;

			foreach ($phpDocMap as $phpDocKey => $resolveCallback) {
				$this->inProcess[$fileName][$phpDocKey] = false;
				$this->inProcess[$fileName][$phpDocKey] = $data = $resolveCallback();
				$phpDocMap[$phpDocKey] = $data;
			}

		} finally {
			unset($this->inProcess[$fileName]);
		}

		return $phpDocMap;
	}

	/**
	 * @param string $fileName
	 * @param string|null $lookForTrait
	 * @param string|null $traitUseClass
	 * @return callable[]
	 */
	private function createFilePhpDocMap(
		string $fileName,
		?string $lookForTrait,
		?string $traitUseClass
	): array
	{
		/** @var callable[] $phpDocMap */
		$phpDocMap = [];

		/** @var (callable(): TemplateTypeMap)[] $typeMapStack */
		$typeMapStack = [];

		/** @var string[] $classStack */
		$classStack = [];
		if ($lookForTrait !== null && $traitUseClass !== null) {
			$classStack[] = $traitUseClass;
		}
		$namespace = null;
		$uses = [];
		$this->processNodes(
			$this->phpParser->parseFile($fileName),
			function (\PhpParser\Node $node) use ($fileName, $lookForTrait, &$phpDocMap, &$classStack, &$namespace, &$uses, &$typeMapStack) {
				$functionName = null;
				if ($node instanceof Node\Stmt\ClassLike) {
					if ($lookForTrait !== null) {
						if (!$node instanceof Node\Stmt\Trait_) {
							return self::SKIP_NODE;
						}
						if ((string) $node->namespacedName !== $lookForTrait) {
							return self::SKIP_NODE;
						}
					} else {
						if ($node->name === null) {
							if (!$node instanceof Node\Stmt\Class_) {
								throw new \PHPStan\ShouldNotHappenException();
							}

							$className = $this->anonymousClassNameHelper->getAnonymousClassName($node, $fileName);
						} elseif ((bool) $node->getAttribute('anonymousClass', false)) {
							$className = $node->name->name;
						} else {
							$className = ltrim(sprintf('%s\\%s', $namespace, $node->name->name), '\\');
						}
						$classStack[] = $className;
					}
				} elseif ($node instanceof Node\Stmt\TraitUse) {
					foreach ($node->traits as $traitName) {
						$traitName = (string) $traitName;
						$traitExists = trait_exists($traitName);
						if ($traitExists === false || $traitExists === null) {
							continue;
						}

						$traitReflection = new \ReflectionClass($traitName);
						if ($traitReflection->getFileName() === false) {
							continue;
						}
						if (!file_exists($traitReflection->getFileName())) {
							continue;
						}

						$className = $classStack[count($classStack) - 1] ?? null;
						if ($className === null) {
							throw new \PHPStan\ShouldNotHappenException();
						}

						$traitPhpDocMap = $this->createFilePhpDocMap(
							$traitReflection->getFileName(),
							$traitName,
							$className
						);
						$phpDocMap = array_merge($phpDocMap, $traitPhpDocMap);
					}
					return null;
				} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
					$namespace = (string) $node->name;
					return null;
				} elseif ($node instanceof \PhpParser\Node\Stmt\Use_ && $node->type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL) {
					foreach ($node->uses as $use) {
						$uses[strtolower($use->getAlias()->name)] = (string) $use->name;
					}
					return null;
				} elseif ($node instanceof \PhpParser\Node\Stmt\GroupUse) {
					$prefix = (string) $node->prefix;
					foreach ($node->uses as $use) {
						if ($node->type !== \PhpParser\Node\Stmt\Use_::TYPE_NORMAL && $use->type !== \PhpParser\Node\Stmt\Use_::TYPE_NORMAL) {
							continue;
						}

						$uses[strtolower($use->getAlias()->name)] = sprintf('%s\\%s', $prefix, (string) $use->name);
					}
					return null;
				} elseif ($node instanceof Node\Stmt\ClassMethod) {
					$functionName = $node->name->name;
				} elseif ($node instanceof Node\Stmt\Function_) {
					$functionName = ltrim(sprintf('%s\\%s', $namespace, $node->name->name), '\\');
				} elseif (!in_array(get_class($node), [
					Node\Stmt\Property::class,
					Node\Stmt\Foreach_::class,
					Node\Expr\Assign::class,
					Node\Expr\AssignRef::class,
					Node\Stmt\Class_::class,
					Node\Stmt\ClassConst::class,
					Node\Stmt\Static_::class,
					Node\Stmt\Echo_::class,
					Node\Stmt\Return_::class,
					Node\Stmt\Expression::class,
					Node\Stmt\Throw_::class,
					Node\Stmt\If_::class,
					Node\Stmt\While_::class,
					Node\Stmt\Switch_::class,
					Node\Stmt\Nop::class,
				], true)) {
					return null;
				}

				$phpDocString = CommentHelper::getDocComment($node);
				if ($phpDocString === null) {
					return null;
				}

				$className = $classStack[count($classStack) - 1] ?? null;
				$typeMapCb = $typeMapStack[count($typeMapStack) - 1] ?? null;

				$phpDocKey = $this->getPhpDocKey($className, $lookForTrait, $phpDocString);
				$phpDocMap[$phpDocKey] = function () use ($phpDocString, $namespace, $uses, $className, $functionName, $typeMapCb): ResolvedPhpDocBlock {
					$nameScope = new NameScope(
						$namespace,
						$uses,
						$className,
						$functionName,
						$typeMapCb !== null ? $typeMapCb() : TemplateTypeMap::createEmpty()
					);
					return $this->phpDocStringResolver->resolve($phpDocString, $nameScope);
				};

				if (!($node instanceof Node\Stmt\ClassLike) && !($node instanceof Node\FunctionLike)) {
					return null;
				}

				$typeMapStack[] = function () use ($fileName, $className, $lookForTrait, $phpDocString, $typeMapCb): TemplateTypeMap {
					static $typeMap = null;
					if ($typeMap !== null) {
						return $typeMap;
					}
					$resolvedPhpDoc = $this->getResolvedPhpDoc(
						$fileName,
						$className,
						$lookForTrait,
						$phpDocString
					);
					return new TemplateTypeMap(array_merge(
						$typeMapCb !== null ? $typeMapCb()->getTypes() : [],
						array_map(static function (Type $type): Type {
							return TemplateTypeHelper::toArgument($type);
						}, $resolvedPhpDoc->getTemplateTypeMap()->getTypes())
					));
				};

				return self::POP_TYPE_MAP_STACK;
			},
			static function (\PhpParser\Node $node, $callbackResult) use ($lookForTrait, &$namespace, &$classStack, &$uses, &$typeMapStack): void {
				if ($node instanceof Node\Stmt\ClassLike && $lookForTrait === null) {
					if (count($classStack) === 0) {
						throw new \PHPStan\ShouldNotHappenException();
					}
					array_pop($classStack);
				} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
					$namespace = null;
					$uses = [];
				}
				if ($callbackResult !== self::POP_TYPE_MAP_STACK) {
					return;
				}

				if (count($typeMapStack) === 0) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				array_pop($typeMapStack);
			}
		);

		if (count($typeMapStack) > 0) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $phpDocMap;
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node|scalar $node
	 * @param \Closure(\PhpParser\Node $node): mixed $nodeCallback
	 * @param \Closure(\PhpParser\Node $node, mixed $callbackResult): void $endNodeCallback
	 */
	private function processNodes($node, \Closure $nodeCallback, \Closure $endNodeCallback): void
	{
		if ($node instanceof Node) {
			$callbackResult = $nodeCallback($node);
			if ($callbackResult === self::SKIP_NODE) {
				return;
			}
			foreach ($node->getSubNodeNames() as $subNodeName) {
				$subNode = $node->{$subNodeName};
				$this->processNodes($subNode, $nodeCallback, $endNodeCallback);
			}
			$endNodeCallback($node, $callbackResult);
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$this->processNodes($subNode, $nodeCallback, $endNodeCallback);
			}
		}
	}

	private function getPhpDocKey(
		?string $class,
		?string $trait,
		string $docComment
	): string
	{
		$docComment = \Nette\Utils\Strings::replace($docComment, '#\s+#', ' ');

		return md5(sprintf('%s-%s-%s', $class, $trait, $docComment));
	}

}
