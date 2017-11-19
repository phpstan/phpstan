<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\Cache\Cache;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocStringResolver;

class FileTypeMapper
{

	/** @var \PHPStan\Parser\Parser */
	private $phpParser;

	/** @var \PHPStan\PhpDoc\PhpDocStringResolver */
	private $phpDocStringResolver;

	/** @var \PHPStan\Cache\Cache */
	private $cache;

	/** @var mixed[][] */
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

	public function getResolvedPhpDoc(string $filename, string $docComment): array
	{
		$map = $this->getResolvedPhpDocMap($filename);
		$key = md5($docComment);

		if (!isset($map[$key])) { // most likely wrong $fileName due to traits
			return $this->phpDocStringResolver->resolve('/** nothing */', new NameScope(null, []));
		}

		return $map[$key];
	}

	private function getResolvedPhpDocMap(string $fileName): array
	{
		if (!isset($this->memoryCache[$fileName])) {
			$cacheKey = sprintf('%s-%d-v16', $fileName, filemtime($fileName));
			$map = $this->cache->load($cacheKey);

			if ($map === null) {
				$map = $this->createResolvedPhpDocMap($fileName);
				$this->cache->save($cacheKey, $map);
			}

			$this->memoryCache[$fileName] = $map;
		}

		return $this->memoryCache[$fileName];
	}

	private function createResolvedPhpDocMap(string $fileName): array
	{
		$phpDocMap = [];

		/** @var \PhpParser\Node\Stmt\ClassLike|null $lastClass */
		$lastClass = null;
		$namespace = null;
		$uses = [];
		$nameScope = null;

		$this->processNodes(
			$this->phpParser->parseFile($fileName),
			function (\PhpParser\Node $node) use (&$phpDocMap, &$lastClass, &$namespace, &$uses, &$nameScope) {
				if ($node instanceof Node\Stmt\ClassLike) {
					$lastClass = $node;
				} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
					$namespace = (string) $node->name;
					$nameScope = null;
				} elseif ($node instanceof \PhpParser\Node\Stmt\Use_ && $node->type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL) {
					foreach ($node->uses as $use) {
						$uses[$use->alias] = (string) $use->name;
					}
					$nameScope = null;
				} elseif ($node instanceof \PhpParser\Node\Stmt\GroupUse) {
					$prefix = (string) $node->prefix;
					foreach ($node->uses as $use) {
						if ($node->type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL || $use->type === \PhpParser\Node\Stmt\Use_::TYPE_NORMAL) {
							$uses[$use->alias] = sprintf('%s\\%s', $prefix, $use->name);
						}
					}
					$nameScope = null;
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

				$className = $lastClass !== null ? $lastClass->name : null;
				if ($className !== null && $namespace !== null) {
					$className = sprintf('%s\\%s', $namespace, $className);
				}

				$nameScope = new NameScope($namespace, $uses, $className);
				$resolvedPhpDoc = $this->phpDocStringResolver->resolve($phpDocString, $nameScope);
				$phpDocMap[md5($phpDocString)] = $resolvedPhpDoc;
			}
		);

		return $phpDocMap;
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node $node
	 * @param \Closure $nodeCallback
	 */
	private function processNodes($node, \Closure $nodeCallback)
	{
		if ($node instanceof Node) {
			$nodeCallback($node);
			foreach ($node->getSubNodeNames() as $subNodeName) {
				$subNode = $node->{$subNodeName};
				$this->processNodes($subNode, $nodeCallback);
			}
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$this->processNodes($subNode, $nodeCallback);
			}
		}
	}

}
