<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\HasPropertyType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;

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

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Constant\ConstantStringType[]
	 */
	public static function getConstantStrings(Type $type): array
	{
		return self::map(ConstantStringType::class, $type);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\ConstantType[]
	 */
	public static function getConstantTypes(Type $type): array
	{
		return self::map(ConstantType::class, $type);
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
	 * @return string[]
	 */
	public static function getDirectClassNames(Type $type): array
	{
		if ($type instanceof TypeWithClassName) {
			return [$type->getClassName()];
		}

		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			$classNames = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof TypeWithClassName) {
					continue;
				}

				$classNames[] = $innerType->getClassName();
			}

			return $classNames;
		}

		return [];
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

	public static function toBenevolentUnion(Type $type): Type
	{
		if ($type instanceof BenevolentUnionType) {
			return $type;
		}

		if ($type instanceof UnionType) {
			return new BenevolentUnionType($type->getTypes());
		}

		return $type;
	}

	public static function findThisType(Type $type): ?ThisType
	{
		if ($type instanceof ThisType) {
			return $type;
		}

		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			foreach ($type->getTypes() as $innerType) {
				$thisType = self::findThisType($innerType);
				if ($thisType !== null) {
					return $thisType;
				}
			}
		}

		return null;
	}

	public static function hasGeneralArray(Type $type): bool
	{
		if ($type instanceof HasOffsetType) {
			return true;
		}

		if ($type instanceof ArrayType && !$type instanceof ConstantArrayType) {
			return true;
		}

		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			foreach ($type->getTypes() as $innerType) {
				if ($innerType instanceof ArrayType && !$innerType instanceof ConstantArrayType) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param Type $type
	 * @return HasPropertyType[]
	 */
	public static function getHasPropertyTypes(Type $type): array
	{
		if ($type instanceof HasPropertyType) {
			return [$type];
		}

		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			$hasPropertyTypes = [];
			foreach ($type->getTypes() as $innerType) {
				$hasPropertyTypes = array_merge($hasPropertyTypes, self::getHasPropertyTypes($innerType));
			}
			return $hasPropertyTypes;
		}

		return [];
	}

}
