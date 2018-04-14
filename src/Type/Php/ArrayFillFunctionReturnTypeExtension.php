<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;

class ArrayFillFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_fill';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 3) {
			return $functionReflection->getReturnType();
		}

		$valueType = $scope->getType($functionCall->args[2]->value);
		$startIndexType = $scope->getType($functionCall->args[0]->value);
		if (!$startIndexType instanceof ConstantIntegerType) {
			return new ArrayType(new IntegerType(), $valueType, true);
		}

		$numberType = $scope->getType($functionCall->args[1]->value);
		if (!$numberType instanceof ConstantIntegerType) {
			return new ArrayType(new IntegerType(), $valueType, true);
		}

		$arrayType = new ConstantArrayType([], []);
		$nextIndex = $startIndexType->getValue();
		for ($i = 0; $i < $numberType->getValue(); $i++) {
			$arrayType = $arrayType->setOffsetValueType(
				new ConstantIntegerType($nextIndex),
				$valueType
			);
			if ($nextIndex < 0) {
				$nextIndex = 0;
			} else {
				$nextIndex++;
			}
		}

		return $arrayType;
	}

}
