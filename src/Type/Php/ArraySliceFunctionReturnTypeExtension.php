<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class ArraySliceFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_slice';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$arrayArg = $functionCall->args[0]->value ?? null;

		if ($arrayArg === null) {
			return new ArrayType(
				new IntegerType(),
				new MixedType()
			);
		}

		$valueType = $scope->getType($arrayArg);

		if (isset($functionCall->args[1])) {
			$offset = $scope->getType($functionCall->args[1]->value);
			if (!$offset instanceof ConstantIntegerType) {
				$offset = new ConstantIntegerType(0);
			}
		} else {
			$offset = new ConstantIntegerType(0);
		}

		if (isset($functionCall->args[2])) {
			$limit = $scope->getType($functionCall->args[2]->value);
			if (!$limit instanceof ConstantIntegerType) {
				$limit = new NullType();
			}
		} else {
			$limit = new NullType();
		}

		$constantArrays = TypeUtils::getConstantArrays($valueType);
		if (count($constantArrays) === 0) {
			return $valueType;
		}

		if (isset($functionCall->args[3])) {
			$preserveKeys = $scope->getType($functionCall->args[3]->value);
			$preserveKeys = (new ConstantBooleanType(true))->isSuperTypeOf($preserveKeys)->yes();
		} else {
			$preserveKeys = false;
		}

		$arrayTypes = array_map(static function (ConstantArrayType $constantArray) use ($offset, $limit, $preserveKeys): ConstantArrayType {
			return $constantArray->slice($offset->getValue(), $limit->getValue(), $preserveKeys);
		}, $constantArrays);

		return TypeCombinator::union(...$arrayTypes);
	}

}
