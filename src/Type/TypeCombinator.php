<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypeCombinator
{

	public static function addNull(Type $type): Type
	{
		if ($type instanceof MixedType || $type instanceof NullType) {
			return $type;
		}

		if (!$type instanceof UnionType) {
			return new CommonUnionType([$type, new NullType()], false);
		}

		if (!self::containsNull($type)) {
			$innerTypes = array_merge($type->getTypes(), [new NullType()]);
			if ($type instanceof UnionIterableType) {
				return new UnionIterableType(
					$type->getItemType(),
					false,
					$innerTypes
				);
			}

			return new CommonUnionType(
				$innerTypes,
				false
			);
		}

		return $type;
	}

	public static function removeNull(Type $type): Type
	{
		if (
			$type instanceof MixedType
			|| $type instanceof NullType
			|| !$type instanceof UnionType
		) {
			return $type;
		}

		$newInnerTypes = [];
		foreach ($type->getTypes() as $innerType) {
			if ($innerType instanceof NullType) {
				continue;
			}

			$newInnerTypes[] = $innerType;
		}

		if ($type instanceof UnionIterableType) {
			return new UnionIterableType($type->getItemType(), false, $newInnerTypes);
		}

		return new CommonUnionType($newInnerTypes, false);
	}

	private static function containsNull(UnionType $unionType): bool
	{
		foreach ($unionType->getTypes() as $type) {
			if ($type instanceof NullType) {
				return true;
			}
		}

		return false;
	}

}
