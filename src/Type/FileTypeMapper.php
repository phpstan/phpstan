<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\Parser\Parser;

class FileTypeMapper
{

	const CONST_FETCH_CONSTANT = '__PHPSTAN_CLASS_REFLECTION_CONSTANT__';
	const TYPE_PATTERN = '((?:(?:\$this|\\\?[0-9a-zA-Z_]+)(?:\[\])*(?:\|)?)+)';

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var bool */
	private $enableUnionTypes;

	/** @var mixed[] */
	private $memoryCache = [];

	public function __construct(
		Parser $parser,
		\Nette\Caching\Cache $cache,
		bool $enableUnionTypes
	)
	{
		$this->parser = $parser;
		$this->cache = $cache;
		$this->enableUnionTypes = $enableUnionTypes;
	}

	public function getTypeMap(string $fileName): array
	{
		$cacheKey = sprintf('%s-%d-v22-%d', $fileName, filemtime($fileName), $this->enableUnionTypes ? 1 : 0);
		if (isset($this->memoryCache[$cacheKey])) {
			return $this->memoryCache[$cacheKey];
		}
		$cachedResult = $this->cache->load($cacheKey);
		if ($cachedResult === null) {
			$typeMap = $this->createTypeMap($fileName);
			$this->cache->save($cacheKey, $typeMap);
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
		$typeParts = explode('|', $typeString);
		$typePartsWithoutNull = array_values(array_filter($typeParts, function ($part) {
			return strtolower($part) !== 'null';
		}));
		if (count($typePartsWithoutNull) === 0) {
			return new NullType();
		}

		$isNullable = count($typeParts) !== count($typePartsWithoutNull);
		if (count($typePartsWithoutNull) > 1) {
			$otherTypes = [];

			/** @var \PHPStan\Type\IterableType $iterableType */
			$iterableType = null;
			$onlyOneItemType = true;
			foreach ($typePartsWithoutNull as $typePart) {
				$type = TypehintHelper::getTypeObjectFromTypehint($typePart, false, $className, $nameScope);
				if ($type instanceof IterableType) {
					if ($iterableType !== null) {
						if ($onlyOneItemType) {
							$otherTypes[] = $iterableType;
						}
						$otherTypes[] = $type;
						$onlyOneItemType = false;
					} else {
						$iterableType = $type;
					}
				} else {
					$otherTypes[] = $type;
				}
			}

			if ($iterableType !== null && $onlyOneItemType) {
				return new UnionIterableType($iterableType->getItemType(), $isNullable, $otherTypes);
			}

			if ($this->enableUnionTypes) {
				return new CommonUnionType($otherTypes, $isNullable);
			}

			return new MixedType();
		}

		return TypehintHelper::getTypeObjectFromTypehint($typePartsWithoutNull[0], $isNullable, $className, $nameScope);
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
