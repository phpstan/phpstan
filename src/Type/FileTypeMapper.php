<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\Cache\Cache;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;

class FileTypeMapper
{

	/** @var \PHPStan\Parser\Parser */
	private $phpParser;

	/** @var \PHPStan\PhpDoc\PhpDocStringResolver */
	private $phpDocStringResolver;

	/** @var \PHPStan\Cache\Cache */
	private $cache;

	/** @var \PHPStan\PhpDoc\ResolvedPhpDocBlock[][] */
	private $memoryCache = [];

	/** @var (false|callable|\PHPStan\PhpDoc\ResolvedPhpDocBlock)[][] */
	private $inProcess = [];

	public function __construct(
		Parser $phpParser,
		PhpDocStringResolver $phpDocStringResolver,
		Cache $cache
	)
	{
		$this->phpParser = $phpParser;
		$this->phpDocStringResolver = $phpDocStringResolver;
		$this->cache = $cache;
	}

	public function getResolvedPhpDoc(string $fileName, string $className = null, string $docComment): ResolvedPhpDocBlock
	{
		$phpDocKey = md5($className . $docComment);
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
			$cacheKey = sprintf('%s-%d-v31', $fileName, filemtime($fileName));
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
		$phpDocMap = [];

		/** @var string[] $classStack */
		$classStack = [];
		$namespace = null;
		$uses = [];

		$this->processNodes(
			$this->phpParser->parseFile($fileName),
			function (\PhpParser\Node $node) use (&$phpDocMap, &$classStack, &$namespace, &$uses): void {
				if ($node instanceof Node\Stmt\ClassLike) {
					$classStack[] = ltrim(sprintf('%s\\%s', $namespace, $node->name), '\\');
				} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
					$namespace = (string) $node->name;
				} elseif ($node instanceof \PhpParser\Node\Stmt\Use_ && $node->type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL) {
					foreach ($node->uses as $use) {
						$uses[$use->alias] = (string) $use->name;
					}
				} elseif ($node instanceof \PhpParser\Node\Stmt\GroupUse) {
					$prefix = (string) $node->prefix;
					foreach ($node->uses as $use) {
						if ($node->type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL || $use->type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL) {
							$uses[$use->alias] = sprintf('%s\\%s', $prefix, $use->name);
						}
					}
				} elseif (!in_array(get_class($node), [
					Node\Stmt\Property::class,
					Node\Stmt\ClassMethod::class,
					Node\Stmt\Function_::class,
					Node\Stmt\Foreach_::class,
					Node\Expr\Assign::class,
					Node\Expr\AssignRef::class,
					Node\Stmt\Class_::class,
				], true)) {
					return;
				}

				$phpDocString = CommentHelper::getDocComment($node);
				if ($phpDocString === null) {
					return;
				}

				$className = $classStack[count($classStack) - 1] ?? null;
				$nameScope = new NameScope($namespace, $uses, $className);
				$phpDocKey = md5($className . $phpDocString);
				$phpDocMap[$phpDocKey] = function () use ($phpDocString, $nameScope): ResolvedPhpDocBlock {
					return $this->phpDocStringResolver->resolve($phpDocString, $nameScope);
				};
			},
			function (\PhpParser\Node $node) use (&$namespace, &$classStack, &$uses): void {
				if ($node instanceof Node\Stmt\ClassLike) {
					if (count($classStack) === 0) {
						throw new \PHPStan\ShouldNotHappenException();
					}
					array_pop($classStack);
				} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
					$namespace = null;
					$uses = [];
				}
			}
		);

		try {
			$this->inProcess[$fileName] = $phpDocMap;

			foreach ($phpDocMap as $phpDocKey => $resolveCallback) {
				$this->inProcess[$fileName][$phpDocKey] = false;
				$this->inProcess[$fileName][$phpDocKey] = $resolveCallback();
				$phpDocMap[$phpDocKey] = $this->inProcess[$fileName][$phpDocKey];
			}

		} finally {
			unset($this->inProcess[$fileName]);
		}

		return $phpDocMap;
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node|scalar $node
	 * @param \Closure $nodeCallback
	 * @param \Closure $endNodeCallback
	 */
	private function processNodes($node, \Closure $nodeCallback, \Closure $endNodeCallback): void
	{
		if ($node instanceof Node) {
			$nodeCallback($node);
			foreach ($node->getSubNodeNames() as $subNodeName) {
				$subNode = $node->{$subNodeName};
				$this->processNodes($subNode, $nodeCallback, $endNodeCallback);
			}
			$endNodeCallback($node);
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$this->processNodes($subNode, $nodeCallback, $endNodeCallback);
			}
		}
	}

}
