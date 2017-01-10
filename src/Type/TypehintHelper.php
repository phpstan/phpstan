<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\NameScope;

class TypehintHelper
{

	use ClassTypeHelperTrait;

	public static function getTypeObjectFromTypehint(
		string $typehintString,
		bool $isNullable,
		string $selfClass = null,
		NameScope $nameScope = null
	): Type
	{
		if (strrpos($typehintString, '[]') === strlen($typehintString) - 2) {
			$arr = new ArrayType(self::getTypeObjectFromTypehint(
				substr($typehintString, 0, -2),
				false,
				$selfClass,
				$nameScope
			), $isNullable);
			return $arr;
		}

		if ($selfClass !== null) {
			if ($typehintString === 'static') {
				return new StaticType($selfClass, $isNullable);
			} elseif ($typehintString === 'self') {
				return new ObjectType($selfClass, $isNullable);
			} elseif ($typehintString === '$this') {
				return new ThisType($selfClass, $isNullable);
			} elseif ($typehintString === 'parent') {
				if (self::exists($selfClass)) {
					$classReflection = new \ReflectionClass($selfClass);
					if ($classReflection->getParentClass() !== false) {
						return new ObjectType($classReflection->getParentClass()->getName(), false);
					}
				}

				return new NonexistentParentClassType(false);
			}
		} elseif ($typehintString === 'parent') {
			return new NonexistentParentClassType(false);
		}

		$lowercasedTypehintString = strtolower($typehintString);
		switch ($lowercasedTypehintString) {
			case 'int':
			case 'integer':
				return new IntegerType($isNullable);
			case 'bool':
			case 'boolean':
				return new TrueOrFalseBooleanType($isNullable);
			case 'true':
				return new TrueBooleanType($isNullable);
			case 'false':
				return new FalseBooleanType($isNullable);
			case 'string':
				return new StringType($isNullable);
			case 'float':
				return new FloatType($isNullable);
			case 'array':
				return new ArrayType(new MixedType(), $isNullable);
			case 'iterable':
				return new IterableIterableType(new MixedType(), $isNullable);
			case 'callable':
				return new CallableType($isNullable);
			case 'null':
				return new NullType();
			case 'resource':
				return new ResourceType($isNullable);
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
				return new ObjectType($className, $isNullable);
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
			$reflectionType->allowsNull(),
			$selfClass
		);

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
					$phpDocType = new IterableIterableType(
						$phpDocType->getItemType(),
						$type->isNullable() || $phpDocType->isNullable()
					);
				} elseif ($type instanceof ArrayType) {
					$type = new ArrayType(
						$phpDocType->getItemType(),
						$type->isNullable() || $phpDocType->isNullable()
					);
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
