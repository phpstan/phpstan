<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class ArrayFilterFunctionReturnTypeReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_filter';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$arrayArg = $functionCall->args[0]->value ?? null;
		$callbackArg = $functionCall->args[1]->value ?? null;
		$flagArg = $functionCall->args[2]->value ?? null;

		if ($arrayArg !== null) {
			$arrayArgType = $scope->getType($arrayArg);
			$keyType = $arrayArgType->getIterableKeyType();
			$itemType = $arrayArgType->getIterableValueType();

			if ($callbackArg === null) {
				return TypeCombinator::union(
					...array_map([$this, 'removeFalsey'], TypeUtils::getArrays($arrayArgType))
				);
			}

			if ($flagArg === null && $callbackArg instanceof Closure && count($callbackArg->stmts) === 1) {
				$statement = $callbackArg->stmts[0];
				if ($statement instanceof Return_ && $statement->expr !== null && count($callbackArg->params) > 0) {
					if (!$callbackArg->params[0]->var instanceof Variable || !is_string($callbackArg->params[0]->var->name)) {
						throw new \PHPStan\ShouldNotHappenException();
					}
					$itemVariableName = $callbackArg->params[0]->var->name;
					$scope = $scope->assignVariable($itemVariableName, $itemType, TrinaryLogic::createYes());
					$scope = $scope->filterByTruthyValue($statement->expr);
					$itemType = $scope->getVariableType($itemVariableName);
				}
			}

		} else {
			$keyType = new MixedType();
			$itemType = new MixedType();
		}

		return new ArrayType($keyType, $itemType);
	}

	public function removeFalsey(Type $type): Type
	{
		$falseyTypes = new UnionType([
			new NullType(),
			new ConstantBooleanType(false),
			new ConstantIntegerType(0),
			new ConstantFloatType(0.0),
			new ConstantStringType(''),
			new ConstantArrayType([], []),
		]);

		if ($type instanceof ConstantArrayType) {
			$keys = $type->getKeyTypes();
			$values = $type->getValueTypes();

			$generalize = false;

			foreach ($values as $offset => $value) {
				$isFalsey = $falseyTypes->isSuperTypeOf($value);

				if ($isFalsey->yes()) {
					unset($keys[$offset], $values[$offset]);
				} elseif ($isFalsey->maybe()) {
					$values[$offset] = TypeCombinator::remove($values[$offset], $falseyTypes);
					$generalize = true;
				}
			}

			$filteredArray = new ConstantArrayType(array_values($keys), array_values($values));

			return $generalize ? $filteredArray->generalize() : $filteredArray;
		}

		$keyType = $type->getIterableKeyType();
		$valueType = $type->getIterableValueType();

		$valueType = TypeCombinator::remove($valueType, $falseyTypes);

		if ($valueType instanceof NeverType) {
			return new ConstantArrayType([], []);
		}

		return new ArrayType($keyType, $valueType);
	}

}
