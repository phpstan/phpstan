<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class ResetFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'reset';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (count($functionCall->args) === 0) {
			return $functionReflection->getReturnType();
		}

		$argType = $scope->getType($functionCall->args[0]->value);
		if ($argType instanceof ConstantArrayType) {
			$keyTypes = $argType->getKeyTypes();
			if (count($keyTypes) === 0) {
				return new ConstantBooleanType(false);
			}

			return $argType->getOffsetValueType($keyTypes[0]);
		} elseif ($argType instanceof ArrayType) {
			return TypeCombinator::union(
				$argType->getItemType(),
				new ConstantBooleanType(false)
			);
		}

		return $functionReflection->getReturnType();
	}

}
