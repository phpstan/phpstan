<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

final class StrSplitFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'str_split';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$defaultReturnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();

		if (count($functionCall->args) < 1) {
			return $defaultReturnType;
		}

		$splitLength = 1;
		if (count($functionCall->args) >= 2) {
			$splitLengthType = $scope->getType($functionCall->args[1]->value);
			if (!$splitLengthType instanceof ConstantIntegerType) {
				return $defaultReturnType;
			}
			$splitLength = $splitLengthType->getValue();
			if ($splitLength < 1) {
				return new ConstantBooleanType(false);
			}
		}

		$stringType = $scope->getType($functionCall->args[0]->value);
		if (!$stringType instanceof ConstantStringType) {
			return new ArrayType(new IntegerType(), new StringType());
		}
		$stringValue = $stringType->getValue();

		$items = str_split($stringValue, $splitLength);
		if (!is_array($items)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return self::createConstantArrayFrom($items, $scope);
	}

	private static function createConstantArrayFrom(array $constantArray, Scope $scope): ConstantArrayType
	{
		$keyTypes = [];
		$valueTypes = [];
		$isList = true;
		$i = 0;

		foreach ($constantArray as $key => $value) {
			$keyType = $scope->getTypeFromValue($key);
			if (!$keyType instanceof ConstantIntegerType) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$keyTypes[] = $keyType;

			$valueTypes[] = $scope->getTypeFromValue($value);

			$isList = $isList && $key === $i;
			$i++;
		}

		return new ConstantArrayType($keyTypes, $valueTypes, $isList ? $i : 0);
	}

}
