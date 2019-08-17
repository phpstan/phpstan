<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Accessory\AccessoryType;
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
		return self::map(ArrayType::class, $type, true);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Constant\ConstantArrayType[]
	 */
	public static function getConstantArrays(Type $type): array
	{
		return self::map(ConstantArrayType::class, $type, false);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Constant\ConstantStringType[]
	 */
	public static function getConstantStrings(Type $type): array
	{
		return self::map(ConstantStringType::class, $type, false);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\ConstantType[]
	 */
	public static function getConstantTypes(Type $type): array
	{
		return self::map(ConstantType::class, $type, false);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\ConstantType[]
	 */
	public static function getAnyConstantTypes(Type $type): array
	{
		return self::map(ConstantType::class, $type, false, false);
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\ArrayType[]
	 */
	public static function getAnyArrays(Type $type): array
	{
		return self::map(ArrayType::class, $type, true, false);
	}

	public static function generalizeType(Type $type): Type
	{
		if ($type instanceof ConstantType) {
			return $type->generalize();
		} elseif ($type instanceof UnionType) {
			return TypeCombinator::union(...array_map(static function (Type $innerType): Type {
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
		return self::map(ConstantScalarType::class, $type, false);
	}

	/**
	 * @param string $typeClass
	 * @param Type $type
	 * @param bool $inspectIntersections
	 * @param bool $stopOnUnmatched
	 * @return mixed[]
	 */
	private static function map(
		string $typeClass,
		Type $type,
		bool $inspectIntersections,
		bool $stopOnUnmatched = true
	): array
	{
		if ($type instanceof $typeClass) {
			return [$type];
		}

		if ($type instanceof UnionType) {
			$matchingTypes = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof $typeClass) {
					if ($stopOnUnmatched) {
						return [];
					}

					continue;
				}

				$matchingTypes[] = $innerType;
			}

			return $matchingTypes;
		}

		if ($inspectIntersections && $type instanceof IntersectionType) {
			$matchingTypes = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof $typeClass) {
					if ($stopOnUnmatched) {
						return [];
					}

					continue;
				}

				$matchingTypes[] = $innerType;
			}

			return $matchingTypes;
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

	/**
	 * @param Type $type
	 * @return Type[]
	 */
	public static function flattenTypes(Type $type): array
	{
		if ($type instanceof UnionType) {
			return $type->getTypes();
		}

		return [$type];
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
			$hasPropertyTypes = [[]];
			foreach ($type->getTypes() as $innerType) {
				$hasPropertyTypes[] = self::getHasPropertyTypes($innerType);
			}

			return array_merge(...$hasPropertyTypes);
		}

		return [];
	}

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Accessory\AccessoryType[]
	 */
	public static function getAccessoryTypes(Type $type): array
	{
		return self::map(AccessoryType::class, $type, true, false);
	}

	public static function containsCallable(Type $type): bool
	{
		if ($type->isCallable()->yes()) {
			return true;
		}

		if ($type instanceof UnionType) {
			foreach ($type->getTypes() as $innerType) {
				if ($innerType->isCallable()->yes()) {
					return true;
				}
			}
		}

		return false;
	}

}
