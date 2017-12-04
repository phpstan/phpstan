<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;

class TypehintHelper
{

	private static function getTypeObjectFromTypehint(string $typeString, string $selfClass = null): Type
	{
		switch (strtolower($typeString)) {
			case 'int':
				return new IntegerType();
			case 'bool':
				return new TrueOrFalseBooleanType();
			case 'string':
				return new StringType();
			case 'float':
				return new FloatType();
			case 'array':
				return new ArrayType(new MixedType(), new MixedType());
			case 'iterable':
				return new IterableIterableType(new MixedType(), new MixedType());
			case 'callable':
				return new CallableType();
			case 'void':
				return new VoidType();
			case 'object':
				return new ObjectWithoutClassType();
			case 'self':
				return $selfClass !== null ? new ObjectType($selfClass) : new ErrorType();
			case 'parent':
				$broker = Broker::getInstance();
				if ($selfClass !== null && $broker->hasClass($selfClass)) {
					$classReflection = $broker->getClass($selfClass);
					if ($classReflection->getParentClass() !== false) {
						return new ObjectType($classReflection->getParentClass()->getName());
					}
				}
				return new NonexistentParentClassType();
			default:
				return new ObjectType($typeString);
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
			return $phpDocType ?? new MixedType();
		}

		$reflectionTypeString = (string) $reflectionType;
		if (\Nette\Utils\Strings::endsWith(strtolower($reflectionTypeString), '\\object')) {
			$reflectionTypeString = 'object';
		}
		$type = self::getTypeObjectFromTypehint($reflectionTypeString, $selfClass);
		if ($reflectionType->allowsNull()) {
			$type = TypeCombinator::addNull($type);
		}

		if ($isVariadic) {
			$type = new ArrayType(new IntegerType(), $type);
		}

		return self::decideType($type, $phpDocType);
	}

	public static function decideType(
		Type $type,
		Type $phpDocType = null
	): Type
	{
		if ($phpDocType !== null && !$phpDocType instanceof ErrorType) {
			if ($type instanceof VoidType || $phpDocType instanceof VoidType) {
				return new VoidType();
			}

			if (TypeCombinator::removeNull($type) instanceof IterableIterableType) {
				if ($phpDocType instanceof UnionType) {
					$innerTypes = [];
					foreach ($phpDocType->getTypes() as $innerType) {
						if ($innerType instanceof ArrayType) {
							$innerTypes[] = new IterableIterableType(
								$innerType->getIterableKeyType(),
								$innerType->getIterableValueType()
							);
						} else {
							$innerTypes[] = $innerType;
						}
					}
					$phpDocType = new UnionType($innerTypes);
				} elseif ($phpDocType instanceof ArrayType) {
					$phpDocType = new IterableIterableType(
						$phpDocType->getIterableKeyType(),
						$phpDocType->getIterableValueType()
					);
				}
			}

			return $type->isSuperTypeOf($phpDocType)->yes() ? $phpDocType : $type;
		}

		return $type;
	}

}
