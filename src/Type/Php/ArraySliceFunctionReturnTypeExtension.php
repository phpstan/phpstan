<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;

class ArraySliceFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_slice';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$arrayArg = $functionCall->args[0]->value ?? null;

		if ($arrayArg === null) {
			return new ArrayType(
				new IntegerType(),
				new MixedType()
			);
		}

		$valueType = $scope->getType($arrayArg);

		if ( ! $valueType instanceof ArrayType) {
			return new ArrayType(
				new IntegerType(),
				new MixedType()
			);
		}

		if (isset($functionCall->args[1])) {
			$offset = $scope->getType($functionCall->args[1]->value);
			if ( ! $offset instanceof ConstantScalarType) {
				$offset = new ConstantIntegerType(0);
			}
		} else {
			$offset = new ConstantIntegerType(0);
		}

		if (isset($functionCall->args[2])) {
			$limit = $scope->getType($functionCall->args[2]->value);
			if ( ! $limit instanceof ConstantScalarType) {
				$limit = new NullType();
			}
		} else {
			$limit = new NullType();
		}

		if (isset($functionCall->args[3])) {
			$preserveKeys = (new ConstantBooleanType(true))->isSuperTypeOf($scope->getType($functionCall->args[3]->value))->yes();
		} else {
			$preserveKeys = false;
		}

		if ($valueType instanceof ConstantArrayType) {
			$slice = $valueType->slice($offset->getValue(), $limit->getValue());
			if ($preserveKeys) {
				return $slice;
			}

			return $slice->getValuesArray();
		}

		if ($preserveKeys) {
			return $valueType;
		}

		return $valueType->getValuesArray();
	}

}
