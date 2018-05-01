<?php declare(strict_types = 1);

namespace PHPStan\Type;

class CompoundTypeHelper
{

	public static function accepts(CompoundType $compoundType, Type $otherType): bool
	{
		if ($compoundType instanceof MixedType) {
			return true;
		}

		if ($compoundType instanceof UnionType) {
			$isArraysOnly = true;
			foreach ($compoundType->getTypes() as $innerType) {
				if (!$innerType instanceof ArrayType) {
					$isArraysOnly = false;
					break;
				}
			}

			if ($isArraysOnly) {
				if (TypeCombinator::shouldSkipUnionTypeAccepts($compoundType)) {
					return true;
				}

				foreach ($compoundType->getTypes() as $innerType) {
					if (!$otherType->accepts($innerType)) {
						return false;
					}
				}

				return true;
			}
		}

		return $compoundType->isSubTypeOf($otherType)->yes();
	}

}
