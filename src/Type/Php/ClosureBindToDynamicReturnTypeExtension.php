<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;

class ClosureBindToDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return \Closure::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'bindTo';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		$closureType = $scope->getType($methodCall->var);
		if (!($closureType instanceof ClosureType)) {
			return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		}

		return $closureType;
	}

}
