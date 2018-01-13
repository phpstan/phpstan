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

	/** @var \PHPStan\PhpDoc\ResolvedPhpDocBlock[][][] */
	private $memoryCache = [];

	/** @var (false|callable|ResolvedPhpDocBlock)[][][]  */
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

	public function getResolvedPhpDoc(string $filename, string $className = null, string $docComment): ResolvedPhpDocBlock
	{
		$key = md5($docComment);

		if (isset($this->inProcess[$filename])) {
			if (isset($this->inProcess[$filename][$className][$key])) {
				$data = $this->inProcess[$filename][$className][$key];
				if (is_callable($data)) {
					$this->inProcess[$filename][$className][$key] = false;
					$this->inProcess[$filename][$className][$key] = $data();

				} elseif ($data === false) { // PHPDoc has cyclic dependency
					return $this->phpDocStringResolver->resolve('/** nothing */', new NameScope(null, []));
				}

				assert($this->inProcess[$filename][$className][$key] instanceof ResolvedPhpDocBlock);
				return $this->inProcess[$filename][$className][$key];
			}
		}

		$map = $this->getResolvedPhpDocMap($filename, $className);
		if (!isset($map[$key])) { // most likely wrong $fileName due to traits
			return $this->phpDocStringResolver->resolve('/** nothing */', new NameScope(null, []));
		}

		return $map[$key];
	}

	/**
	 * @param string $fileName
	 * @param string|null $className
	 * @return \PHPStan\PhpDoc\ResolvedPhpDocBlock[]
	 */
	private function getResolvedPhpDocMap(string $fileName, string $className = null): array
	{
		if (!isset($this->memoryCache[$fileName])) {
			$cacheKey = sprintf('%s-%d-v27', $fileName, filemtime($fileName));
			$map = $this->cache->load($cacheKey);

			if ($map === null) {
				$map = $this->createResolvedPhpDocMap($fileName);
				$this->cache->save($cacheKey, $map);
			}

			$this->memoryCache[$fileName] = $map;
		}

		if (!array_key_exists($className, $this->memoryCache[$fileName])) {
			// class with traits - class has no phpDocs but trait has some
			return [];
		}

		return $this->memoryCache[$fileName][$className];
	}

	/**
	 * @param string $fileName
	 * @return \PHPStan\PhpDoc\ResolvedPhpDocBlock[][]
	 */
	private function createResolvedPhpDocMap(string $fileName): array
	{
		$phpDocMap = [];

		/** @var \PhpParser\Node\Stmt\ClassLike[] $classStack */
		$classStack = [];
		$namespace = null;
		$uses = [];

		$this->processNodes(
			$this->phpParser->parseFile($fileName),
			function (\PhpParser\Node $node) use (&$phpDocMap, &$classStack, &$namespace, &$uses) {
				if ($node instanceof Node\Stmt\ClassLike) {
					$classStack[] = $node;
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

				$className = count($classStack) > 0 ? $classStack[count($classStack) - 1]->name : null;
				if ($className !== null && $namespace !== null) {
					$className = sprintf('%s\\%s', $namespace, $className);
				}

				$nameScope = new NameScope($namespace, $uses, $className);
				$phpDocMap[$className][md5($phpDocString)] = function () use ($phpDocString, $nameScope): ResolvedPhpDocBlock {
					return $this->phpDocStringResolver->resolve($phpDocString, $nameScope);
				};
			},
			function (\PhpParser\Node $node) use (&$namespace, &$classStack, &$uses) {
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

			foreach ($phpDocMap as $className => $classMap) {
				foreach ($classMap as $phpDocKey => $resolveCallback) {
					$this->inProcess[$fileName][$className][$phpDocKey] = false;
					$this->inProcess[$fileName][$className][$phpDocKey] = $resolveCallback();
					$phpDocMap[$className][$phpDocKey] = $this->inProcess[$fileName][$className][$phpDocKey];
				}
			}

		} finally {
			unset($this->inProcess[$fileName]);
		}

		return $phpDocMap;
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node $node
	 * @param \Closure $nodeCallback
	 * @param \Closure $endNodeCallback
	 */
	private function processNodes($node, \Closure $nodeCallback, \Closure $endNodeCallback)
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
