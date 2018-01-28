<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\TrinaryLogic;

trait NonCallableTypeTrait
{

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

}
