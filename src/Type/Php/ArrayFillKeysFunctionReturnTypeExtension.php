<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

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
		$constantArrays = TypeUtils::getConstantArrays($keysType);
		if (count($constantArrays) === 0) {
			return new ArrayType($keysType->getIterableValueType(), $valueType, true);
		}

		$arrayTypes = [];
		foreach ($constantArrays as $constantArray) {
			$arrayType = new ConstantArrayType([], []);
			foreach ($constantArray->getValueTypes() as $keyType) {
				$arrayType = $arrayType->setOffsetValueType($keyType, $valueType);
			}
			$arrayTypes[] = $arrayType;
		}

		return TypeCombinator::union(...$arrayTypes);
	}

}
