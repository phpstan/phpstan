<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class MicrotimeFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'microtime';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 1) {
			return new StringType();
		}

		$argType = $scope->getType($functionCall->args[0]->value);
		$isTrueType = (new ConstantBooleanType(true))->isSuperTypeOf($argType);
		$isFalseType = (new ConstantBooleanType(false))->isSuperTypeOf($argType);
		$compareTypes = $isTrueType->compareTo($isFalseType);
		if ($compareTypes === $isTrueType) {
			return new FloatType();
		}
		if ($compareTypes === $isFalseType) {
			return new StringType();
		}

		return TypeCombinator::union(new StringType(), new FloatType());
	}

}
