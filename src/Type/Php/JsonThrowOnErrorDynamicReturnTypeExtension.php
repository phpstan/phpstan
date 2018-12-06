<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class JsonThrowOnErrorDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	/** @var array<string, int> */
	private $argumentPositions = [
		'json_encode' => 1,
		'json_decode' => 3,
	];

	public function isFunctionSupported(
		FunctionReflection $functionReflection
	): bool
	{
		return defined('JSON_THROW_ON_ERROR') && in_array(
			$functionReflection->getName(),
			[
				'json_encode',
				'json_decode',
			],
			true
		);
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$argumentPosition = $this->argumentPositions[$functionReflection->getName()];
		$defaultReturnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		if (!isset($functionCall->args[$argumentPosition])) {
			return $defaultReturnType;
		}

		$valueType = $scope->getType($functionCall->args[$argumentPosition]->value);
		if (!$valueType instanceof ConstantIntegerType) {
			return $defaultReturnType;
		}

		$value = $valueType->getValue();
		if (($value & JSON_THROW_ON_ERROR) !== JSON_THROW_ON_ERROR) {
			return $defaultReturnType;
		}

		return TypeCombinator::remove($defaultReturnType, new ConstantBooleanType(false));
	}

}
