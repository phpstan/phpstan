<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

interface CompoundType extends Type
{

	public function isSubTypeOf(Type $otherType): TrinaryLogic;

}
