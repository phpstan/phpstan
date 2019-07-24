<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class ArrayValuesFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_values';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$arrayArg = $functionCall->args[0]->value ?? null;
		if ($arrayArg !== null) {
			$valueType = $scope->getType($arrayArg);
			if ($valueType->isArray()->yes()) {
				if ($valueType instanceof ConstantArrayType) {
					return $valueType->getValuesArray();
				}
				return TypeCombinator::intersect(new ArrayType(new IntegerType(), $valueType->getIterableValueType()), ...TypeUtils::getAccessoryTypes($valueType));
			}
		}

		return new ArrayType(
			new IntegerType(),
			new MixedType()
		);
	}

}
