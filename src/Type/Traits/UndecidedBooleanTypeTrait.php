<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Type\BooleanType;

trait UndecidedBooleanTypeTrait
{

	public function toBoolean(): BooleanType
	{
		return new BooleanType();
	}

}
