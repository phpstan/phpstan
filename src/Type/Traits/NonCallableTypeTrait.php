<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;

trait NonCallableTypeTrait
{

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getCallableParametersAcceptor(Scope $scope): ParametersAcceptor
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

}
