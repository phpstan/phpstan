<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class RangeFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	private const RANGE_LENGTH_THRESHOLD = 50;

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'range';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 2) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$startType = $scope->getType($functionCall->args[0]->value);
		$endType = $scope->getType($functionCall->args[1]->value);
		$stepType = count($functionCall->args) >= 3 ? $scope->getType($functionCall->args[2]->value) : new ConstantIntegerType(1);

		$constantReturnTypes = [];

		$startConstants = TypeUtils::getConstantScalars($startType);
		foreach ($startConstants as $startConstant) {
			if (!$startConstant instanceof ConstantIntegerType && !$startConstant instanceof ConstantFloatType) {
				continue;
			}

			$endConstants = TypeUtils::getConstantScalars($endType);
			foreach ($endConstants as $endConstant) {
				if (!$endConstant instanceof ConstantIntegerType && !$endConstant instanceof ConstantFloatType) {
					continue;
				}

				$stepConstants = TypeUtils::getConstantScalars($stepType);
				foreach ($stepConstants as $stepConstant) {
					if (!$stepConstant instanceof ConstantIntegerType && !$stepConstant instanceof ConstantFloatType) {
						continue;
					}

					$rangeLength = (int) ceil(abs($startConstant->getValue() - $endConstant->getValue()) / $stepConstant->getValue()) + 1;
					if ($rangeLength > self::RANGE_LENGTH_THRESHOLD) {
						continue;
					}

					$keyTypes = [];
					$valueTypes = [];

					$rangeValues = range($startConstant->getValue(), $endConstant->getValue(), $stepConstant->getValue());
					foreach ($rangeValues as $key => $value) {
						$keyTypes[] = new ConstantIntegerType($key);
						$valueTypes[] = $scope->getTypeFromValue($value);
					}

					$constantReturnTypes[] = new ConstantArrayType($keyTypes, $valueTypes, $rangeLength);
				}
			}
		}

		if (count($constantReturnTypes) > 0) {
			return TypeCombinator::union(...$constantReturnTypes);
		}

		$startType = TypeUtils::generalizeType($startType);
		$endType = TypeUtils::generalizeType($endType);
		$stepType = TypeUtils::generalizeType($stepType);

		if (
			$startType instanceof IntegerType
			&& $endType instanceof IntegerType
			&& $stepType instanceof IntegerType
		) {
			return new ArrayType(new IntegerType(), new IntegerType());
		}

		if (
			$startType instanceof FloatType
			|| $endType instanceof FloatType
			|| $stepType instanceof FloatType
		) {
			return new ArrayType(new IntegerType(), new FloatType());
		}

		return new ArrayType(new IntegerType(), new UnionType([new IntegerType(), new FloatType()]));
	}

}
