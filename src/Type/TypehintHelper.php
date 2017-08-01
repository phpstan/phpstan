<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\NameScope;
use PHPStan\Broker\Broker;

class TypehintHelper
{

	public static function getTypeObjectFromTypehint(
		string $typehintString,
		string $selfClass = null,
		NameScope $nameScope = null,
		bool $fromReflection = false
	): Type
	{
		if (
			!$fromReflection
			&& strrpos($typehintString, '[]') === strlen($typehintString) - 2
		) {
			$arr = new ArrayType(self::getTypeObjectFromTypehint(
				substr($typehintString, 0, -2),
				$selfClass,
				$nameScope
			));
			return $arr;
		}

		$lowercasedTypehintString = strtolower($typehintString);
		if ($selfClass !== null) {
			if ($lowercasedTypehintString === 'static' && !$fromReflection) {
				return new StaticType($selfClass);
			} elseif ($lowercasedTypehintString === 'self') {
				return new ObjectType($selfClass);
			} elseif ($typehintString === '$this' && !$fromReflection) {
				return new ThisType($selfClass);
			} elseif ($lowercasedTypehintString === 'parent') {
				$broker = Broker::getInstance();
				if ($broker->hasClass($selfClass)) {
					$classReflection = $broker->getClass($selfClass);
					if ($classReflection->getParentClass() !== false) {
						return new ObjectType($classReflection->getParentClass()->getName());
					}
				}

				return new NonexistentParentClassType();
			}
		} elseif ($lowercasedTypehintString === 'parent') {
			return new NonexistentParentClassType();
		}

		switch (true) {
			case $lowercasedTypehintString === 'int':
			case $lowercasedTypehintString === 'integer' && !$fromReflection:
				return new IntegerType();
			case $lowercasedTypehintString === 'bool':
			case $lowercasedTypehintString === 'boolean' && !$fromReflection:
				return new TrueOrFalseBooleanType();
			case $lowercasedTypehintString === 'true' && !$fromReflection:
				return new TrueBooleanType();
			case $lowercasedTypehintString === 'false' && !$fromReflection:
				return new FalseBooleanType();
			case $lowercasedTypehintString === 'string':
				return new StringType();
			case $lowercasedTypehintString === 'float':
			case $lowercasedTypehintString === 'double' && !$fromReflection:
				return new FloatType();
			case $lowercasedTypehintString === 'scalar' && !$fromReflection:
				return new CommonUnionType([new IntegerType(), new FloatType(), new StringType(), new TrueOrFalseBooleanType()]);
			case $lowercasedTypehintString === 'array':
				return new ArrayType(new MixedType());
			case $lowercasedTypehintString === 'iterable':
				return new IterableIterableType(new MixedType());
			case $lowercasedTypehintString === 'callable':
				return new CallableType();
			case $lowercasedTypehintString === 'null' && !$fromReflection:
				return new NullType();
			case $lowercasedTypehintString === 'resource' && !$fromReflection:
				return new ResourceType();
			case $lowercasedTypehintString === 'object' && !$fromReflection:
			case $lowercasedTypehintString === 'mixed' && !$fromReflection:
				return new MixedType(true);
			case $lowercasedTypehintString === 'void':
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
		$type = self::getTypeObjectFromTypehint(
			$reflectionTypeString,
			$selfClass,
			null,
			true
		);
		if ($reflectionType->allowsNull()) {
			$type = TypeCombinator::addNull($type);
		}

		if ($isVariadic) {
			$type = new ArrayType($type);
		}

		return self::decideType($type, $phpDocType);
	}

	public static function decideType(
		Type $type,
		Type $phpDocType = null
	): Type
	{
		if ($phpDocType !== null) {
			if ($type->isIterable() === TrinaryLogic::YES && $phpDocType instanceof ArrayType) {
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
