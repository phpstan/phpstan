<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;

class CallbackBasedFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

    /** @var int[] */
    private $functionNames = [
        'array_reduce' => 1,
    ];

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return isset($this->functionNames[$functionReflection->getName()]);
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
    {
        $argumentPosition = $this->functionNames[$functionReflection->getName()];

        if (!isset($functionCall->args[$argumentPosition])) {
            return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }

        $callbackType = $scope->getType($functionCall->args[$argumentPosition]->value);
        if ($callbackType->isCallable()->no()) {
            return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }

        return ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $functionCall->args,
            $callbackType->getCallableParametersAcceptors($scope)
        )->getReturnType();
    }
}
