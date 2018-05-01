<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class CountFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'count';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (count($functionCall->args) < 1) {
			return $functionReflection->getReturnType();
		}

		$constantArrays = TypeUtils::getConstantArrays($scope->getType($functionCall->args[0]->value));
		if (count($constantArrays) === 0) {
			return $functionReflection->getReturnType();
		}
		$countTypes = [];
		foreach ($constantArrays as $array) {
			$countTypes[] = new ConstantIntegerType($array->count());
		}

		return TypeCombinator::union(...$countTypes);
	}

}
