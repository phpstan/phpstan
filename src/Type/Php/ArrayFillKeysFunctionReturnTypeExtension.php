<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Type;

class ArrayFillKeysFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_fill_keys';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 2) {
			return $functionReflection->getReturnType();
		}

		$valueType = $scope->getType($functionCall->args[1]->value);
		$keysType = $scope->getType($functionCall->args[0]->value);
		if (!$keysType instanceof ConstantArrayType) {
			return new ArrayType($keysType->getIterableValueType(), $valueType, true);
		}

		$arrayType = new ConstantArrayType([], []);
		foreach ($keysType->getValueTypes() as $keyType) {
			$arrayType = $arrayType->setOffsetValueType($keyType, $valueType);
		}

		return $arrayType;
	}

}
