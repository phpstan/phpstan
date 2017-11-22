<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class ArrayFilterFunctionReturnTypeReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return strcasecmp($functionReflection->getName(), 'array_filter') === 0;
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

			if ($flagArg === null && $callbackArg instanceof Closure && count($callbackArg->stmts) === 1) {
				$statement = $callbackArg->stmts[0];
				if ($statement instanceof Return_ && $statement->expr !== null && count($callbackArg->params) > 0) {
					$itemVariableName = $callbackArg->params[0]->name;
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

}
