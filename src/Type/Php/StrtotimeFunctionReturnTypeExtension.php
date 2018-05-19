<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;

class StrtotimeFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'strtotime';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$result = array_unique(array_map(function (ConstantStringType $string): bool {
			return is_int(strtotime($string->getValue()));
		}, TypeUtils::getConstantStrings($scope->getType($functionCall->args[0]->value))));

		if (count($result) !== 1) {
			return ParametersAcceptorSelector::selectFromArgs(
				$scope,
				$functionCall->args,
				$functionReflection->getVariants()
			)->getReturnType();
		}

		return $result[0] ? new IntegerType() : new ConstantBooleanType(false);
	}

}
