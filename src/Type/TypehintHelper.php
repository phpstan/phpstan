<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\NameScope;

class TypehintHelper
{

	use ClassTypeHelperTrait;

	public static function getTypeObjectFromTypehint(
		string $typehintString,
		string $selfClass = null,
		NameScope $nameScope = null
	): Type
	{
		if (strrpos($typehintString, '[]') === strlen($typehintString) - 2) {
			$arr = new ArrayType(self::getTypeObjectFromTypehint(
				substr($typehintString, 0, -2),
				$selfClass,
				$nameScope
			));
			return $arr;
		}

		if ($selfClass !== null) {
			if ($typehintString === 'static') {
				return new StaticType($selfClass);
			} elseif ($typehintString === 'self') {
				return new ObjectType($selfClass);
			} elseif ($typehintString === '$this') {
				return new ThisType($selfClass);
			} elseif ($typehintString === 'parent') {
				if (self::exists($selfClass)) {
					$classReflection = new \ReflectionClass($selfClass);
					if ($classReflection->getParentClass() !== false) {
						return new ObjectType($classReflection->getParentClass()->getName());
					}
				}

				return new NonexistentParentClassType();
			}
		} elseif ($typehintString === 'parent') {
			return new NonexistentParentClassType();
		}

		$lowercasedTypehintString = strtolower($typehintString);
		switch ($lowercasedTypehintString) {
			case 'int':
			case 'integer':
				return new IntegerType();
			case 'bool':
			case 'boolean':
				return new TrueOrFalseBooleanType();
			case 'true':
				return new TrueBooleanType();
			case 'false':
				return new FalseBooleanType();
			case 'string':
				return new StringType();
			case 'float':
				return new FloatType();
			case 'scalar':
				return new CommonUnionType([new IntegerType(), new FloatType(), new StringType(), new TrueOrFalseBooleanType()]);
			case 'array':
				return new ArrayType(new MixedType());
			case 'iterable':
				return new IterableIterableType(new MixedType());
			case 'callable':
				return new CallableType();
			case 'null':
				return new NullType();
			case 'resource':
				return new ResourceType();
			case 'object':
			case 'mixed':
				return new MixedType();
			case 'void':
				return new VoidType();
			default:
				$className = $typehintString;
				if ($nameScope !== null) {
					$className = $nameScope->resolveStringName($className);
				}
				return new ObjectType($className);
		}
	}

	public static function decideTypeFromReflection(
		\ReflectionType $reflectionType = null,
		Type $phpDocType = null,
		string $selfClass = null,
		bool $isVariadic = false
	): Type
	{
		if ($reflectionType === null) {
			return $phpDocType !== null ? $phpDocType : new MixedType();
		}

		$reflectionTypeString = (string) $reflectionType;
		if ($isVariadic) {
			$reflectionTypeString .= '[]';
		}

		$type = self::getTypeObjectFromTypehint(
			$reflectionTypeString,
			$selfClass
		);
		if ($reflectionType->allowsNull()) {
			$type = TypeCombinator::addNull($type);
		}

		return self::decideType($type, $phpDocType);
	}

	public static function decideType(
		Type $type,
		Type $phpDocType = null
	): Type
	{
		if ($phpDocType !== null) {
			if ($type instanceof IterableType && $phpDocType instanceof ArrayType) {
				if ($type instanceof IterableIterableType) {
					$phpDocType = new IterableIterableType($phpDocType->getItemType());
				} elseif ($type instanceof ArrayType) {
					$type = new ArrayType($phpDocType->getItemType());
				}
			} elseif ($phpDocType instanceof UnionType) {
				if ($phpDocType->accepts($type)) {
					return $phpDocType;
				}
			}
			if ($type->accepts($phpDocType)) {
				return $phpDocType;
			}
		}

		return $type;
	}

	/**
	 * @param \PHPStan\Type\Type[] $typeMap
	 * @param string $docComment
	 * @return \PHPStan\Type\Type|null
	 */
	public static function getReturnTypeFromPhpDoc(array $typeMap, string $docComment)
	{
		$returnTypeString = self::getReturnTypeStringFromMethod($docComment);
		if ($returnTypeString !== null && isset($typeMap[$returnTypeString])) {
			return $typeMap[$returnTypeString];
		}

		return null;
	}

	/**
	 * @param string $docComment
	 * @return string|null
	 */
	private static function getReturnTypeStringFromMethod(string $docComment)
	{
		$count = preg_match_all('#@return\s+' . FileTypeMapper::TYPE_PATTERN . '#', $docComment, $matches);
		if ($count !== 1) {
			return null;
		}

		return $matches[1][0];
	}

	/**
	 * @param \PHPStan\Type\Type[] $typeMap
	 * @param string[] $parameterNames
	 * @param string $docComment
	 * @return \PHPStan\Type\Type[]
	 */
	public static function getParameterTypesFromPhpDoc(
		array $typeMap,
		array $parameterNames,
		string $docComment
	): array
	{
		preg_match_all('#@param\s+' . FileTypeMapper::TYPE_PATTERN . '\s+\$([a-zA-Z0-9_]+)#', $docComment, $matches, PREG_SET_ORDER);
		$phpDocParameterTypeStrings = [];
		foreach ($matches as $match) {
			$typeString = $match[1];
			$parameterName = $match[2];
			if (!isset($phpDocParameterTypeStrings[$parameterName])) {
				$phpDocParameterTypeStrings[$parameterName] = [];
			}

			$phpDocParameterTypeStrings[$parameterName][] = $typeString;
		}

		$phpDocParameterTypes = [];
		foreach ($parameterNames as $parameterName) {
			$typeString = self::getParameterAnnotationTypeString($phpDocParameterTypeStrings, $parameterName);
			if ($typeString !== null && isset($typeMap[$typeString])) {
				$phpDocParameterTypes[$parameterName] = $typeMap[$typeString];
			}
		}

		return $phpDocParameterTypes;
	}

	/**
	 * @param mixed[] $phpDocParameterTypeStrings
	 * @param string $parameterName
	 * @return string|null
	 */
	private static function getParameterAnnotationTypeString(array $phpDocParameterTypeStrings, string $parameterName)
	{
		if (!isset($phpDocParameterTypeStrings[$parameterName])) {
			return null;
		}

		$typeStrings = $phpDocParameterTypeStrings[$parameterName];
		if (count($typeStrings) > 1) {
			return null;
		}

		return $typeStrings[0];
	}

}
