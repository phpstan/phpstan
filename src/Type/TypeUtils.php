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
		if ($type instanceof ArrayType) {
			return [$type];
		}

		if ($type instanceof UnionType) {
			$arrays = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof ArrayType) {
					return [];
				}

				$arrays[] = $innerType;
			}

			return $arrays;
		}

		return [];
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Constant\ConstantArrayType[]
	 */
	public static function getConstantArrays(Type $type): array
	{
		if ($type instanceof ConstantArrayType) {
			return [$type];
		}

		if ($type instanceof UnionType) {
			$constantArrays = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof ConstantArrayType) {
					return [];
				}

				$constantArrays[] = $innerType;
			}

			return $constantArrays;
		}

		return [];
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
		if ($type instanceof ConstantScalarType) {
			return [$type];
		}

		if ($type instanceof UnionType) {
			$constantScalarValues = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof ConstantScalarType) {
					return [];
				}

				$constantScalarValues[] = $innerType;
			}

			return $constantScalarValues;
		}

		return [];
	}

}
