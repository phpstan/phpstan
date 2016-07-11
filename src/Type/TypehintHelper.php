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
		if (strpos($typehintString, '[]') !== false) {
			return new ArrayType($isNullable);
		}

		if ($typehintString === 'static') {
			return new StaticType($isNullable);
		}

		if ($typehintString === 'self' && $selfClass !== null) {
			return new ObjectType($selfClass, $isNullable);
		}

		switch ($typehintString) {
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
				return new ArrayType($isNullable);
			case 'callable':
				return new CallableType($isNullable);
			case null:
				return new MixedType(true);
			case 'object':
			case 'mixed':
				return new MixedType($isNullable);
			default:
				return new ObjectType($typehintString, $isNullable);
		}
	}

}
