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
		$map = $this->getResolvedPhpDocMap($filename, $className);
		$key = md5($docComment);

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
			$cacheKey = sprintf('%s-%d-v19', $fileName, filemtime($fileName));
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
				$resolvedPhpDoc = $this->phpDocStringResolver->resolve($phpDocString, $nameScope);
				$phpDocMap[$className][md5($phpDocString)] = $resolvedPhpDoc;
			},
			function (\PhpParser\Node $node) use (&$namespace, &$classStack, &$uses) {
				if ($node instanceof Node\Stmt\ClassLike) {
					array_pop($classStack);
				} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
					$namespace = null;
					$uses = [];
				}
			}
		);

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
