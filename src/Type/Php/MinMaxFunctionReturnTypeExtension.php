<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

class MinMaxFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
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
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		if (count($functionCall->args) === 1) {
			$argType = $scope->getType($functionCall->args[0]->value);
			if ($argType instanceof ArrayType) {
				$iterableValueType = $argType->getIterableValueType();
				$argumentTypes = [];
				if ($iterableValueType instanceof UnionType) {
					foreach ($iterableValueType->getTypes() as $innerType) {
						$argumentTypes[] = $innerType;
					}
				} else {
					$argumentTypes[] = $iterableValueType;
				}

				return $this->processType(
					$functionReflection->getName(),
					$argumentTypes
				);
			}

			return new ErrorType();
		}

		$argumentTypes = [];
		foreach ($functionCall->args as $arg) {
			$argType = $scope->getType($arg->value);
			if ($arg->unpack) {
				$iterableValueType = $argType->getIterableValueType();
				if ($iterableValueType instanceof UnionType) {
					foreach ($iterableValueType->getTypes() as $innerType) {
						$argumentTypes[] = $innerType;
					}
				} else {
					$argumentTypes[] = $iterableValueType;
				}
				continue;
			}

			$argumentTypes[] = $argType;
		}

		return $this->processType(
			$functionReflection->getName(),
			$argumentTypes
		);
	}

	/**
	 * @param string $functionName
	 * @param \PHPStan\Type\Type[] $types
	 * @return Type
	 */
	private function processType(
		string $functionName,
		array $types
	): Type
	{
		$resultType = null;
		foreach ($types as $type) {
			if (!$type instanceof ConstantType) {
				return TypeCombinator::union(...$types);
			}

			if ($resultType === null) {
				$resultType = $type;
				continue;
			}

			$compareResult = $this->compareTypes($resultType, $type);
			if ($functionName === 'min') {
				if ($compareResult === $type) {
					$resultType = $type;
				}
			} elseif ($functionName === 'max') {
				if ($compareResult === $resultType) {
					$resultType = $type;
				}
			}
		}

		if ($resultType === null) {
			return new ErrorType();
		}

		return $resultType;
	}

	private function compareTypes(
		Type $firstType,
		Type $secondType
	): ?Type
	{
		if (
			$firstType instanceof ConstantArrayType
			&& $secondType instanceof ConstantScalarType
		) {
			return $secondType;
		}

		if (
			$firstType instanceof ConstantScalarType
			&& $secondType instanceof ConstantArrayType
		) {
			return $firstType;
		}

		if (
			$firstType instanceof ConstantArrayType
			&& $secondType instanceof ConstantArrayType
		) {
			if ($secondType->count() < $firstType->count()) {
				return $secondType;
			} elseif ($firstType->count() < $secondType->count()) {
				return $firstType;
			}

			foreach ($firstType->getValueTypes() as $i => $firstValueType) {
				$secondValueType = $secondType->getValueTypes()[$i];
				$compareResult = $this->compareTypes($firstValueType, $secondValueType);
				if ($compareResult === $firstValueType) {
					return $firstType;
				}

				if ($compareResult === $secondValueType) {
					return $secondType;
				}
			}

			return null;
		}

		if (
			$firstType instanceof ConstantScalarType
			&& $secondType instanceof ConstantScalarType
		) {
			if ($secondType->getValue() < $firstType->getValue()) {
				return $secondType;
			}

			if ($firstType->getValue() < $secondType->getValue()) {
				return $firstType;
			}
		}

		return null;
	}

}
