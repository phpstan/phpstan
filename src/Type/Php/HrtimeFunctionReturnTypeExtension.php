<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class HrtimeFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'hrtime';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$arrayType = new ConstantArrayType([new ConstantIntegerType(0), new ConstantIntegerType(1)], [new IntegerType(), new IntegerType()], 2);
		$numberType = TypeCombinator::union(new IntegerType(), new FloatType());

		if (count($functionCall->args) < 1) {
			return $arrayType;
		}

		$argType = $scope->getType($functionCall->args[0]->value);
		$isTrueType = (new ConstantBooleanType(true))->isSuperTypeOf($argType);
		$isFalseType = (new ConstantBooleanType(false))->isSuperTypeOf($argType);
		$compareTypes = $isTrueType->compareTo($isFalseType);
		if ($compareTypes === $isTrueType) {
			return $numberType;
		}
		if ($compareTypes === $isFalseType) {
			return $arrayType;
		}

		return TypeCombinator::union($arrayType, $numberType);
	}

}
