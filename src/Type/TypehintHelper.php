<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use ReflectionNamedType;

class TypehintHelper
{

	private static function getTypeObjectFromTypehint(string $typeString, ?string $selfClass): Type
	{
		switch (strtolower($typeString)) {
			case 'int':
				return new IntegerType();
			case 'bool':
				return new BooleanType();
			case 'string':
				return new StringType();
			case 'float':
				return new FloatType();
			case 'array':
				return new ArrayType(new MixedType(), new MixedType());
			case 'iterable':
				return new IterableType(new MixedType(), new MixedType());
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
		?\ReflectionType $reflectionType,
		?Type $phpDocType = null,
		?string $selfClass = null,
		bool $isVariadic = false
	): Type
	{
		if ($reflectionType === null) {
			return $phpDocType ?? new MixedType();
		}

		if (!$reflectionType instanceof ReflectionNamedType) {
			throw new \PHPStan\ShouldNotHappenException(sprintf('Unexpected type: %s', get_class($reflectionType)));
		}

		$reflectionTypeString = $reflectionType->getName();
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
		?Type $phpDocType = null
	): Type
	{
		if ($phpDocType !== null && !$phpDocType instanceof ErrorType) {
			if ($type instanceof VoidType || $phpDocType instanceof VoidType) {
				return new VoidType();
			}

			if (TypeCombinator::removeNull($type) instanceof IterableType) {
				if ($phpDocType instanceof UnionType) {
					$innerTypes = [];
					foreach ($phpDocType->getTypes() as $innerType) {
						if ($innerType instanceof ArrayType) {
							$innerTypes[] = new IterableType(
								$innerType->getKeyType(),
								$innerType->getItemType()
							);
						} else {
							$innerTypes[] = $innerType;
						}
					}
					$phpDocType = new UnionType($innerTypes);
				} elseif ($phpDocType instanceof ArrayType) {
					$phpDocType = new IterableType(
						$phpDocType->getKeyType(),
						$phpDocType->getItemType()
					);
				}
			}

			$resultType = $type->isSuperTypeOf($phpDocType)->yes() ? $phpDocType : $type;
			if (TypeCombinator::containsNull($type)) {
				$type = TypeCombinator::addNull($resultType);
			} else {
				$type = $resultType;
			}
		}

		return $type;
	}

}
