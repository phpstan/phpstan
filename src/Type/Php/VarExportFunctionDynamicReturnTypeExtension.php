<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;

class VarExportFunctionDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'var_export';
	}

	public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, \PhpParser\Node\Expr\FuncCall $functionCall, \PHPStan\Analyser\Scope $scope): \PHPStan\Type\Type
	{
		if (count($functionCall->args) < 1) {
			return $functionReflection->getReturnType();
		}
		if (count($functionCall->args) < 2) {
			return new NullType();
		}

		$returnArgumentType = $scope->getType($functionCall->args[1]->value);
		if ((new ConstantBooleanType(true))->isSuperTypeOf($returnArgumentType)->yes()) {
			return new StringType();
		}

		return new NullType();
	}

}
