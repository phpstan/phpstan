<?php declare(strict_types = 1);

namespace PHPStan\Build;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class ServiceLocatorDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return \Nette\DI\Container::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return in_array($methodReflection->getName(), [
			'getByType',
			'createInstance',
		], true);
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		if (count($methodCall->args) === 0) {
			return $methodReflection->getReturnType();
		}
		$argType = $scope->getType($methodCall->args[0]->value);
		if (!$argType instanceof ConstantStringType) {
			return $methodReflection->getReturnType();
		}

		$type = new ObjectType($argType->getValue());
		if ($methodReflection->getName() === 'getByType' && count($methodCall->args) >= 2) {
			$argType = $scope->getType($methodCall->args[1]->value);
			if ($argType instanceof ConstantBooleanType && $argType->getValue()) {
				$type = TypeCombinator::addNull($type);
			}
		}

		return $type;
	}

}
