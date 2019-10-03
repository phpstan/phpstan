<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class NumberFormatterParseDynamicMethodReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return \NumberFormatter::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return in_array($methodReflection->getName(), [
			'parse',
		], true);
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		if (count($methodCall->args) === 1) {
			return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		}

		$argType = $scope->getType($methodCall->args[1]->value);
		if (!$argType instanceof ConstantIntegerType) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		switch ($argType->getValue()) {
			case \NumberFormatter::TYPE_INT32:
			case \NumberFormatter::TYPE_INT64:
				return new UnionType([new IntegerType(), new ConstantBooleanType(false)]);
			case \NumberFormatter::TYPE_DOUBLE:
				return new UnionType([new FloatType(), new ConstantBooleanType(false)]);
			default:
				throw new \PHPStan\ShouldNotHappenException();
		}
	}

}
