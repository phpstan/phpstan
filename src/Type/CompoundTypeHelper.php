<?php declare(strict_types = 1);

namespace PHPStan\Type;

class CompoundTypeHelper
{

	public static function accepts(CompoundType $compoundType, Type $otherType): bool
	{
		if ($compoundType instanceof MixedType) {
			return true;
		} elseif ($compoundType instanceof UnionType) {
			return UnionTypeHelper::acceptsAll($otherType, $compoundType);
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

}
