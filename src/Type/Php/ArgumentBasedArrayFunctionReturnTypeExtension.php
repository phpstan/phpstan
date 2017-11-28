<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class ArgumentBasedArrayFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	/** @var int[] */
	private $functionNames = [
		'array_fill' => 2,
		'array_fill_keys' => 1,
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return isset($this->functionNames[strtolower($functionReflection->getName())]);
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$argumentPosition = $this->functionNames[strtolower($functionReflection->getName())];

		if (!isset($functionCall->args[$argumentPosition])) {
			return $functionReflection->getReturnType();
		}

		$argumentValue = $functionCall->args[$argumentPosition]->value;
		return new ArrayType(new MixedType(), $scope->getType($argumentValue), true);
	}

}
