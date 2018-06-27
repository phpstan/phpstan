<?php declare(strict_types = 1);

namespace PHPStan\Type\Traits;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;

trait MaybeCallableTypeTrait
{

    public function isCallable(): TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }

    /**
     * @param \PHPStan\Analyser\Scope $scope
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getCallableParametersAcceptors(Scope $scope): array
    {
        return [new TrivialParametersAcceptor()];
    }
}
