<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;

trait FalseyBooleanTypeTrait
{

	public function toBoolean(): BooleanType
	{
		return new ConstantBooleanType(false);
	}

}
