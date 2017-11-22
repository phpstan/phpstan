<?php declare(strict_types = 1);

namespace PHPStan\Type;

class CompoundTypeHelper
{

	public static function accepts(CompoundType $compoundType, Type $otherType): bool
	{
		if ($compoundType instanceof MixedType) {
			return true;
		}

		return $compoundType->isSubTypeOf($otherType)->yes();
	}

}
