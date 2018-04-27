<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;

trait MaybeCallableTypeTrait
{

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getCallableParametersAcceptor(Scope $scope): ParametersAcceptor
	{
		return new TrivialParametersAcceptor();
	}

}
