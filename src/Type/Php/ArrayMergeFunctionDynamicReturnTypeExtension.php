<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class ArrayMergeFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_merge';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (!isset($functionCall->args[0])) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
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

		return $this->processType($argumentTypes);
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @return Type
	 */
	private function processType(
		array $types
	): Type
	{
		$arrayType = null;
		foreach ($types as $type) {
			if (!$type instanceof ConstantArrayType) {
				return \PHPStan\Type\TypeCombinator::union(...$types);
			}
			if ($arrayType === null) {
				$newArrayBuilder = ConstantArrayTypeBuilder::createEmpty();
				foreach ($type->getKeyTypes() as $i => $keyType) {
					$valueType = $type->getValueTypes()[$i];
					if ($keyType instanceof ConstantIntegerType) {
						$keyType = null;
					}
					$newArrayBuilder->setOffsetValueType(
						$keyType,
						$valueType
					);
				}

				$arrayType = $newArrayBuilder->getArray();
				continue;
			}

			foreach ($type->getValueTypes() as $i => $valueType) {
				$keyType = $type->getKeyTypes()[$i];
				if ($keyType instanceof ConstantIntegerType) {
					$arrayType = $arrayType->setOffsetValueType(null, $valueType);
					continue;
				}

				$arrayType = $arrayType->setOffsetValueType($keyType, $valueType);
			}
		}

		/** @var \PHPStan\Type\Type $arrayType */
		$arrayType = $arrayType;

		return $arrayType;
	}

}
