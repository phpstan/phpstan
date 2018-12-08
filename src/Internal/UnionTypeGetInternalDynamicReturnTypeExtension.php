<?php declare(strict_types = 1);

namespace PHPStan\Internal;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class UnionTypeGetInternalDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return UnionType::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getInternal';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if (count($methodCall->args) < 2) {
			return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		}

		$getterClosureType = $scope->getType($methodCall->args[1]->value);
		return ParametersAcceptorSelector::selectSingle($getterClosureType->getCallableParametersAcceptors($scope))->getReturnType();
	}

}
