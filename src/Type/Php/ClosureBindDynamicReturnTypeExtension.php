<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;

class ClosureBindDynamicReturnTypeExtension implements \PHPStan\Type\DynamicStaticMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return \Closure::class;
	}

	public function isStaticMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'bind';
	}

	public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
	{
		$closureType = $scope->getType($methodCall->args[0]->value);
		if (!($closureType instanceof ClosureType)) {
			return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		}

		return $closureType;
	}

}
