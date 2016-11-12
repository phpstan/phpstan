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

}
