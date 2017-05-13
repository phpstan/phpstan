<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\Parser\Parser;
use PHPStan\TypeX\TypeX;
use PHPStan\TypeX\TypeXFactory;

class FileTypeMapper
{

	const CONST_FETCH_CONSTANT = '__PHPSTAN_CLASS_REFLECTION_CONSTANT__';
	const TYPE_PATTERN = '((?:(?:\$this|\\\?[0-9a-zA-Z_]+)(?:\[\])*(?:\|)?)+)';

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\TypeX\TypeXFactory */
	private $typeFactory;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var mixed[] */
	private $memoryCache = [];

	public function __construct(
		Parser $parser,
		TypeXFactory $typeFactory,
		\Nette\Caching\Cache $cache
	)
	{
		$this->parser = $parser;
		$this->typeFactory = $typeFactory;
		$this->cache = $cache;
	}

	public function getTypeMap(string $fileName): array
	{
		$cacheKey = sprintf('%s-%d-v30', $fileName, filemtime($fileName));
		if (isset($this->memoryCache[$cacheKey])) {
			return $this->memoryCache[$cacheKey];
		}
		$cachedResult = $this->cache->load($cacheKey);
		if ($cachedResult === null) {
			$typeMap = $this->typeFactory->withoutAutoSimplify(function () use ($fileName) {
				return $this->createTypeMap($fileName);
			});
//			$this->cache->save($cacheKey, $typeMap);
			$this->memoryCache[$cacheKey] = $typeMap;
			return $typeMap;
		}

		$this->memoryCache[$cacheKey] = $cachedResult;

		return $cachedResult;
	}

	private function createTypeMap(string $fileName): array
	{
		$typeMap = [];
		$patterns = [
			'#@param\s+' . self::TYPE_PATTERN . '\s+\$[a-zA-Z0-9_]+#',
			'#@var\s+' . self::TYPE_PATTERN . '#',
			'#@var\s+\$[a-zA-Z0-9_]+\s+' . self::TYPE_PATTERN . '#',
			'#@return\s+' . self::TYPE_PATTERN . '#',
			'#@property(?:-read)?\s+' . self::TYPE_PATTERN . '\s+\$[a-zA-Z0-9_]+#',
			'#@method\s+(?:static\s+)?' . self::TYPE_PATTERN . '\s*?[a-zA-Z0-9_]+(?:\(.*\))?#',
		];

		/** @var \PhpParser\Node\Stmt\ClassLike|null $lastClass */
		$lastClass = null;
		$namespace = null;
		$uses = [];
		$nameScope = null;
		$this->processNodes(
			$this->parser->parseFile($fileName),
			function (\PhpParser\Node $node) use ($patterns, &$typeMap, &$lastClass, &$namespace, &$uses, &$nameScope) {
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
					Node\Expr\Assign::class,
					Node\Stmt\Class_::class,
				], true)) {
					return;
				}

				$comment = CommentHelper::getDocComment($node);
				if ($comment === null) {
					return;
				}

				$className = $lastClass !== null ? $lastClass->name : null;
				if ($className !== null && $namespace !== null) {
					$className = sprintf('%s\\%s', $namespace, $className);
				}

				foreach ($patterns as $pattern) {
					preg_match_all($pattern, $comment, $matches, PREG_SET_ORDER);
					foreach ($matches as $match) {
						$typeString = $match[1];
						if (isset($typeMap[$typeString])) {
							continue;
						}

						if ($nameScope === null) {
							$nameScope = new NameScope($namespace, $uses);
						}

						$typeMap[$typeString] = $this->getTypeFromTypeString($typeString, $className, $nameScope);
					}
				}
			}
		);

		return $typeMap;
	}

	private function getTypeFromTypeString(string $typeString, string $className = null, NameScope $nameScope): Type
	{
//		/** @var \PHPStan\Type\Type|null $type */
//		$type = null;
//		foreach (explode('|', $typeString) as $typePart) {
//			$typeFromTypePart = TypehintHelper::getTypeObjectFromTypehint($typePart, $className, $nameScope);
//			if ($type === null) {
//				$type = $typeFromTypePart;
//			} else {
//				$type = TypeCombinator::combine($type, $typeFromTypePart);
//			}
//		}
//
//		return $type;

		$types = [];
		foreach (explode('|', $typeString) as $typePart) {
			$types[] = TypehintHelper::getTypeObjectFromTypehint($typePart, $className, $nameScope);
		}

		return $this->typeFactory->createUnionType(...$types);
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
