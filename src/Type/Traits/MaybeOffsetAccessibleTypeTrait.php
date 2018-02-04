<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

trait MaybeOffsetAccessibleTypeTrait
{

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getOffsetValueType(): Type
	{
		return new MixedType();
	}

}
