<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;

class AllArgumentBasedFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	/** @var string[] */
	private $functionNames = [
		'min' => '',
		'max' => '',
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return isset($this->functionNames[strtolower($functionReflection->getName())]);
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (!isset($functionCall->args[0])) {
			return $functionReflection->getReturnType();
		}

		if ($functionCall->args[0]->unpack) {
			$argumentType = $scope->getType($functionCall->args[0]->value);
			if ($argumentType instanceof ArrayType) {
				return $argumentType->getItemType();
			}
		}

		if (count($functionCall->args) === 1) {
			$argumentType = $scope->getType($functionCall->args[0]->value);
			if ($argumentType instanceof ArrayType) {
				return $argumentType->getItemType();
			}
		}

		$argumentType = null;
		foreach ($functionCall->args as $arg) {
			$argType = $scope->getType($arg->value);
			if ($argumentType === null) {
				$argumentType = $argType;
			} else {
				$argumentType = $argumentType->combineWith($argType);
			}
		}

		/** @var \PHPStan\Type\Type $argumentType */
		$argumentType = $argumentType;

		return $argumentType;
	}

}
