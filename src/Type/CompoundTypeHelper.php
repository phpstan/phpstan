<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class CompoundTypeHelper
{

	public static function accepts(CompoundType $compoundType, Type $otherType, bool $strictTypes): TrinaryLogic
	{
		return $compoundType->isAcceptedBy($otherType, $strictTypes);
	}

}
