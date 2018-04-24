<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class ArrayMapFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_map';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 2) {
			return $functionReflection->getReturnType();
		}

		$valueType = new MixedType();
		$callableType = $scope->getType($functionCall->args[0]->value);
		if (!$callableType->isCallable()->no()) {
			$valueType = $callableType->getCallableParametersAcceptor($scope)->getReturnType();
		}

		$arrayType = $scope->getType($functionCall->args[1]->value);
		if ($arrayType instanceof ConstantArrayType) {
			$returnedArrayType = new ConstantArrayType([], []);
			foreach ($arrayType->getKeyTypes() as $keyType) {
				$returnedArrayType = $returnedArrayType->setOffsetValueType(
					$keyType,
					$valueType
				);
			}

			return $returnedArrayType;
		} elseif ($arrayType instanceof ArrayType) {
			return new ArrayType(
				$arrayType->getKeyType(),
				$valueType
			);
		}

		return new ArrayType(
			new MixedType(),
			$valueType
		);
	}

}
