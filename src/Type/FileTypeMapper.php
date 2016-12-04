<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Parser\Parser;

class FileTypeMapper
{

	const CONST_FETCH_CONSTANT = '__PHPSTAN_CLASS_REFLECTION_CONSTANT__';
	const TYPE_PATTERN = '((?:\\\?[0-9a-zA-Z_]+(?:\[\])*(?:\|)?)+)';

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var mixed[] */
	private $memoryCache = [];

	public function __construct(
		Parser $parser,
		\Nette\Caching\Cache $cache
	)
	{
		$this->parser = $parser;
		$this->cache = $cache;
	}

	public function getTypeMap(string $fileName): array
	{
		$cacheKey = sprintf('%s-%d-v10', $fileName, filemtime($fileName));
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
		$objectTypes = [];
		$typeMap = [];
		$processTypeString = function (string $typeString, string $className = null) use (&$typeMap, &$objectTypes) {
			if (isset($typeMap[$typeString])) {
				return;
			}

			$type = $this->getTypeFromTypeString($typeString, $className);

			if ($type instanceof ArrayType) {
				$nestedItemType = $type->getNestedItemType();
				if ($nestedItemType->getItemType() instanceof ObjectType) {
					if ($nestedItemType->getItemType()->getClass() === $className) {
						$typeMap[$typeString] = ArrayType::createDeepArrayType(
							new NestedArrayItemType(new ObjectType($className, false), $nestedItemType->getDepth()),
							$type->isNullable()
						);
					} else {
						$objectTypes[] = [
							'type' => $nestedItemType->getItemType(),
							'typeString' => $typeString,
							'arrayType' => [
								'depth' => $nestedItemType->getDepth(),
								'nullable' => $type->isNullable(),
							],
						];
					}
				} else {
					$typeMap[$typeString] = $type;
				}

				return;
			}

			if (!($type instanceof ObjectType)) {
				$typeMap[$typeString] = $type;
				return;
			} elseif ($type->getClass() === $className) {
				$typeMap[$typeString] = $type;
				return;
			}

			$objectTypes[] = [
				'type' => $type,
				'typeString' => $typeString,
			];
		};

		$patterns = [
			'#@param\s+' . self::TYPE_PATTERN . '\s+\$[a-zA-Z0-9_]+#',
			'#@var\s+' . self::TYPE_PATTERN . '#',
			'#@var\s+\$[a-zA-Z0-9_]+\s+' . self::TYPE_PATTERN . '#',
			'#@return\s+' . self::TYPE_PATTERN . '#',
		];

		/** @var \PhpParser\Node\Stmt\ClassLike|null $lastClass */
		$lastClass = null;
		$this->processNodes(
			$this->parser->parseFile($fileName),
			function (\PhpParser\Node $node, string $className = null) use ($processTypeString, $patterns, &$lastClass) {
				if ($node instanceof Node\Stmt\ClassLike) {
					$lastClass = $node;
				}

				if (!in_array(get_class($node), [
					Node\Stmt\Property::class,
					Node\Stmt\ClassMethod::class,
					Node\Expr\Assign::class,
				], true)) {
					return;
				}

				$comment = CommentHelper::getDocComment($node);
				if ($comment === null) {
					return;
				}

				foreach ($patterns as $pattern) {
					preg_match_all($pattern, $comment, $matches, PREG_SET_ORDER);
					foreach ($matches as $match) {
						$processTypeString($match[1], $className);
					}
				}
			}
		);

		if (count($objectTypes) === 0) {
			return $typeMap;
		}

		$fileString = file_get_contents($fileName);
		if ($lastClass !== null) {
			$classType = 'class';
			if ($lastClass instanceof Interface_) {
				$classType = 'interface';
			} elseif ($lastClass instanceof Trait_) {
				$classType = 'trait';
			}
			$classTypePosition = strpos($fileString, sprintf('%s %s', $classType, $lastClass->name));
			$nameResolveInfluencingPart = trim(substr($fileString, 0, $classTypePosition));
		} else {
			$nameResolveInfluencingPart = $fileString;
		}
		if (substr($nameResolveInfluencingPart, -strlen('final')) === 'final') {
			$nameResolveInfluencingPart = trim(substr($nameResolveInfluencingPart, 0, -strlen('final')));
		}

		if (substr($nameResolveInfluencingPart, -strlen('abstract')) === 'abstract') {
			$nameResolveInfluencingPart = trim(substr($nameResolveInfluencingPart, 0, -strlen('abstract')));
		}

		$namespace = null;
		$uses = [];
		$this->processNodes(
			$this->parser->parseString($nameResolveInfluencingPart),
			function (\PhpParser\Node $node) use ($processTypeString, $patterns, &$namespace, &$uses) {
				if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
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
				}
			}
		);

		foreach ($objectTypes as $key => $objectType) {
			$objectTypeType = $objectType['type'];
			$objectTypeTypeClass = $objectTypeType->getClass();
			if (preg_match('#^[a-zA-Z_\\\]#', $objectTypeTypeClass) === 0) {
				unset($objectTypes[$key]);
				continue;
			}
			if (strtolower($objectTypeTypeClass) === 'new') {
				unset($objectTypes[$key]);
				continue;
			}
		}

		foreach ($objectTypes as $objectType) {
			if (isset($objectType['arrayType'])) {
				$arrayType = $objectType['arrayType'];
				$typeMap[$objectType['typeString']] = ArrayType::createDeepArrayType(
					new NestedArrayItemType(new ObjectType($this->resolveStringName($namespace, $objectType['type']->getClass(), $uses), false), $arrayType['depth']),
					$arrayType['nullable']
				);
			} else {
				$objectTypeString = $objectType['typeString'];
				$objectTypeType = $objectType['type'];
				$typeMap[$objectTypeString] = new ObjectType($this->resolveStringName($namespace, $objectType['type']->getClass(), $uses), $objectTypeType->isNullable());
			}
		}

		return $typeMap;
	}

	private function resolveStringName(string $namespace = null, string $name, array $uses): string
	{
		if (strpos($name, '\\') === 0) {
			return ltrim($name, '\\');
		}

		$nameParts = explode('\\', $name);
		$firstNamePart = $nameParts[0];
		if (isset($uses[$firstNamePart])) {
			if (count($nameParts) === 1) {
				return $uses[$firstNamePart];
			}
			array_shift($nameParts);
			return sprintf('%s\\%s', $uses[$firstNamePart], implode('\\', $nameParts));
		}

		if ($namespace !== null) {
			return sprintf('%s\\%s', $namespace, $name);
		}

		return $name;
	}

	private function getTypeFromTypeString(string $typeString, string $className = null): Type
	{
		$typeParts = explode('|', $typeString);
		$typePartsWithoutNull = array_values(array_filter($typeParts, function ($part) {
			return strtolower($part) !== 'null';
		}));
		if (count($typePartsWithoutNull) === 0) {
			return new NullType();
		}

		if (count($typePartsWithoutNull) !== 1) {
			return new MixedType(false);
		}

		$isNullable = count($typeParts) !== count($typePartsWithoutNull);

		return TypehintHelper::getTypeObjectFromTypehint($typePartsWithoutNull[0], $isNullable, $className);
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node $node
	 * @param \Closure $nodeCallback
	 * @param string|null $className = null
	 */
	private function processNodes($node, \Closure $nodeCallback, string $className = null)
	{
		if ($node instanceof Node) {
			$nodeCallback($node, $className);
			if ($node instanceof Node\Stmt\ClassLike) {
				if (isset($node->namespacedName)) {
					$className = (string) $node->namespacedName;
				} else {
					$className = $node->name;
				}
			}
			foreach ($node->getSubNodeNames() as $subNodeName) {
				$subNode = $node->{$subNodeName};
				$this->processNodes($subNode, $nodeCallback, $className);
			}
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$this->processNodes($subNode, $nodeCallback, $className);
			}
		}
	}

}
