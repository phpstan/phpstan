<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

class AllArgumentBasedFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	/** @var string[] */
	private $functionNames = [
		'min' => '',
		'max' => '',
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return isset($this->functionNames[$functionReflection->getName()]);
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (!isset($functionCall->args[0])) {
			return $functionReflection->getReturnType();
		}

		if ($functionCall->args[0]->unpack) {
			$argumentType = $scope->getType($functionCall->args[0]->value);
			if ($argumentType instanceof ArrayType) {
				return $this->processType(
					$functionReflection->getName(),
					$argumentType->getItemType()
				);
			}
		}

		if (count($functionCall->args) === 1) {
			$argumentType = $scope->getType($functionCall->args[0]->value);
			if ($argumentType instanceof ArrayType) {
				return $this->processType(
					$functionReflection->getName(),
					$argumentType->getItemType()
				);
			}

			return new ErrorType();
		}

		$argumentTypes = [];
		foreach ($functionCall->args as $arg) {
			$argumentTypes[] = $scope->getType($arg->value);
		}

		return $this->processType(
			$functionReflection->getName(),
			TypeCombinator::union(...$argumentTypes)
		);
	}

	private function processType(string $functionName, Type $type): Type
	{
		if ($type instanceof ConstantScalarType) {
			return $type;
		}

		if ($type instanceof UnionType) {
			$result = null;
			$resultType = null;
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof ConstantScalarType) {
					return $type;
				}

				$value = $innerType->getValue();
				if ($functionName === 'min') {
					if ($resultType === null || $value < $result) {
						$result = $value;
						$resultType = $innerType;
					}
				} else {
					if ($resultType === null || $value > $result) {
						$result = $value;
						$resultType = $innerType;
					}
				}
			}

			if ($resultType === null) {
				return new ErrorType();
			}

			return $resultType;
		}

		return $type;
	}

}
