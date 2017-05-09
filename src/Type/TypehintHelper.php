<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\NameScope;
use PHPStan\TypeX\BaseTypeX;
use PHPStan\TypeX\ErrorType;
use PHPStan\TypeX\TypeX;
use PHPStan\TypeX\TypeXFactory;
use SiblingMethodPrototype\Base;

class TypehintHelper
{

	use ClassTypeHelperTrait;

	public static function getTypeObjectFromTypehint(
		string $typehintString,
		string $selfClass = null,
		NameScope $nameScope = null,
		bool $fromReflection = false
	): TypeX
	{
		$typeFactory = TypeXFactory::getInstance();

		if (
			!$fromReflection
			&& strrpos($typehintString, '[]') === strlen($typehintString) - 2
		) {
			return $typeFactory->createArrayType(null, self::getTypeObjectFromTypehint(
				substr($typehintString, 0, -2),
				$selfClass,
				$nameScope
			));
		}

		$lowercasedTypehintString = strtolower($typehintString);
		if ($selfClass !== null) {
			if ($lowercasedTypehintString === 'static' && !$fromReflection) {
				return $typeFactory->createStaticType($selfClass);
			} elseif ($lowercasedTypehintString === 'self') {
				return $typeFactory->createObjectType($selfClass);
			} elseif ($typehintString === '$this' && !$fromReflection) {
				return $typeFactory->createThisType($selfClass);
			} elseif ($lowercasedTypehintString === 'parent') {
				if (self::exists($selfClass)) {
					$classReflection = new \ReflectionClass($selfClass);
					if ($classReflection->getParentClass() !== false) {
						return $typeFactory->createObjectType($classReflection->getParentClass()->getName());
					}
				}

				return $typeFactory->createErrorType(ErrorType::CLASS_HAS_NO_PARENT);
			}
		} elseif ($lowercasedTypehintString === 'parent') {
			return $typeFactory->createErrorType(ErrorType::CLASS_HAS_NO_PARENT);
		}

		switch (true) {
			case $lowercasedTypehintString === 'int':
			case $lowercasedTypehintString === 'integer' && !$fromReflection:
				return $typeFactory->createIntegerType();
			case $lowercasedTypehintString === 'bool':
			case $lowercasedTypehintString === 'boolean' && !$fromReflection:
			return $typeFactory->createBooleanType();
			case $lowercasedTypehintString === 'true' && !$fromReflection:
				return $typeFactory->createTrueType();
			case $lowercasedTypehintString === 'false' && !$fromReflection:
				return $typeFactory->createFalseType();
			case $lowercasedTypehintString === 'string':
				return $typeFactory->createStringType();
			case $lowercasedTypehintString === 'float':
			case $lowercasedTypehintString === 'double' && !$fromReflection:
				return $typeFactory->createFloatType();
			case $lowercasedTypehintString === 'scalar' && !$fromReflection:
				return $typeFactory->createUnionType(
					$typeFactory->createIntegerType(),
					$typeFactory->createFloatType(),
					$typeFactory->createStringType(),
					$typeFactory->createBooleanType()
				);
			case $lowercasedTypehintString === 'array':
				return $typeFactory->createArrayType();
			case $lowercasedTypehintString === 'iterable':
				return $typeFactory->createIterableType();
			case $lowercasedTypehintString === 'callable':
				return $typeFactory->createCallableType();
			case $lowercasedTypehintString === 'null' && !$fromReflection:
				return $typeFactory->createNullType();
			case $lowercasedTypehintString === 'resource' && !$fromReflection:
				return $typeFactory->createResourceType();
			case $lowercasedTypehintString === 'object' && !$fromReflection:
			case $lowercasedTypehintString === 'mixed' && !$fromReflection:
				return $typeFactory->createMixedType();
			case $lowercasedTypehintString === 'void':
				return $typeFactory->createVoidType();
			default:
				$className = $typehintString;
				if ($nameScope !== null) {
					$className = $nameScope->resolveStringName($className);
				}
				return $typeFactory->createObjectType($className);
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
		if ($isVariadic) {
			$type = new ArrayType($type);
		}

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
		if ($type instanceof BaseTypeX && $phpDocType instanceof BaseTypeX) {
			return $type->intersectWith($phpDocType);
		}

		if ($phpDocType !== null) {
			if ($type instanceof IterableType && ($phpDocType instanceof ArrayType || $phpDocType instanceof \PHPStan\TypeX\ArrayType)) {
				if ($type instanceof IterableIterableType) {
					$phpDocType = new IterableIterableType($phpDocType->getItemType());
				} elseif ($type instanceof ArrayType || $type instanceof \PHPStan\TypeX\ArrayType) {
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
