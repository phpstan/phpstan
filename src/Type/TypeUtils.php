<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Constant\ConstantArrayType;

class TypeUtils
{

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\ArrayType[]
	 */
	public static function getArrays(Type $type): array
	{
		return self::map(ArrayType::class, $type);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Constant\ConstantArrayType[]
	 */
	public static function getConstantArrays(Type $type): array
	{
		return self::map(ConstantArrayType::class, $type);
	}

	public static function generalizeType(Type $type): Type
	{
		if ($type instanceof ConstantType) {
			return $type->generalize();
		} elseif ($type instanceof UnionType) {
			return TypeCombinator::union(...array_map(function (Type $innerType): Type {
				return self::generalizeType($innerType);
			}, $type->getTypes()));
		}

		return $type;
	}

	/**
	 * @param Type $type
	 * @return \PHPStan\Type\ConstantScalarType[]
	 */
	public static function getConstantScalars(Type $type): array
	{
		return self::map(ConstantScalarType::class, $type);
	}

	/**
	 * @param string $typeClass
	 * @param Type $type
	 * @return mixed[]
	 */
	private static function map(string $typeClass, Type $type): array
	{
		if ($type instanceof $typeClass) {
			return [$type];
		}

		if ($type instanceof UnionType) {
			$constantScalarValues = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof $typeClass) {
					return [];
				}

				$constantScalarValues[] = $innerType;
			}

			return $constantScalarValues;
		}

		return [];
	}

}
