<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypehintHelper
{

	public static function getTypeObjectFromTypehint(
		string $typehintString,
		bool $isNullable,
		string $selfClass = null
	): Type
	{
		if (strrpos($typehintString, '[]') === strlen($typehintString) - 2) {
			$arr = new ArrayType(self::getTypeObjectFromTypehint(
				substr($typehintString, 0, -2),
				false,
				$selfClass
			), $isNullable);
			return $arr;
		}

		if ($typehintString === 'static' && $selfClass !== null) {
			return new StaticType($selfClass, $isNullable);
		}

		if ($typehintString === 'self' && $selfClass !== null) {
			return new ObjectType($selfClass, $isNullable);
		}

		$lowercasedTypehintString = strtolower($typehintString);
		switch ($lowercasedTypehintString) {
			case 'int':
			case 'integer':
				return new IntegerType($isNullable);
			case 'bool':
			case 'boolean':
				return new BooleanType($isNullable);
			case 'string':
				return new StringType($isNullable);
			case 'float':
				return new FloatType($isNullable);
			case 'array':
				return new ArrayType(new MixedType(true), $isNullable);
			case 'iterable':
				return new IterableIterableType(new MixedType(true), $isNullable);
			case 'callable':
				return new CallableType($isNullable);
			case null:
				return new MixedType(true);
			case 'resource':
				return new ResourceType($isNullable);
			case 'object':
			case 'mixed':
				return new MixedType($isNullable);
			case 'void':
				return new VoidType();
			default:
				return new ObjectType($typehintString, $isNullable);
		}
	}

	public static function decideType(
		\ReflectionType $reflectionType = null,
		Type $phpDocType = null,
		string $selfClass = null,
		bool $isVariadic = false
	): Type
	{
		if ($reflectionType === null) {
			return $phpDocType !== null ? $phpDocType : new MixedType(true);
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
			}
			if ($type->accepts($phpDocType)) {
				return $phpDocType;
			}
		}

		return $type;
	}

}
