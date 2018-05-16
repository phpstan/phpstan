<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Analyser\Scope;
use PHPStan\TrinaryLogic;

trait NonCallableTypeTrait
{

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getCallableParametersAcceptors(Scope $scope): array
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

}
