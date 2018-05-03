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
			foreach ($compoundType->getTypes() as $innerType) {
				if (!$otherType->accepts($innerType)) {
					return false;
				}
			}
			return true;
		}

		return $compoundType->isSubTypeOf($otherType)->yes();
	}

}
