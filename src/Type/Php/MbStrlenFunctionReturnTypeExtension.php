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

class MbStrlenFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'mb_strlen';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();

		if (count($functionCall->args) < 1) {
			return $returnType;
		}
		if (!isset($functionCall->args[1])) {
			return new IntegerType();
		}

		if (!extension_loaded('mbstring')) {
			return $returnType;
		}

		$result = array_unique(array_map(static function (ConstantStringType $encoding): bool {
			return @mb_strlen('', $encoding->getValue()) !== false; // @ = silence the undocumented warning
		}, TypeUtils::getConstantStrings($scope->getType($functionCall->args[1]->value))));

		if (count($result) !== 1) {
			return $returnType;
		}
		return $result[0] ? new IntegerType() : new ConstantBooleanType(false);
	}

}
