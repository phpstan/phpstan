<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class CompoundTypeHelper
{

	public static function accepts(CompoundType $compoundType, Type $otherType, bool $strictTypes): TrinaryLogic
	{
		if ($compoundType instanceof MixedType) {
			return TrinaryLogic::createYes();
		}

		if ($compoundType instanceof BenevolentUnionType) {
			foreach ($compoundType->getTypes() as $innerType) {
				if ($otherType->accepts($innerType, $strictTypes)->yes()) {
					return TrinaryLogic::createYes();
				}
			}

			return TrinaryLogic::createNo();
		}

		return $compoundType->isSubTypeOf($otherType);
	}

}
