<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class ResetFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'reset';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (count($functionCall->args) === 0) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$argType = $scope->getType($functionCall->args[0]->value);
		$constantArrays = TypeUtils::getConstantArrays($argType);
		if (count($constantArrays) > 0) {
			$keyTypes = [];
			foreach ($constantArrays as $constantArray) {
				$arrayKeyTypes = $constantArray->getKeyTypes();
				if (count($arrayKeyTypes) === 0) {
					$keyTypes[] = new ConstantBooleanType(false);
					continue;
				}

				$keyTypes[] = $constantArray->getOffsetValueType($arrayKeyTypes[0]);
			}

			return TypeCombinator::union(...$keyTypes);
		}

		return TypeCombinator::union(
			$argType->getIterableValueType(),
			new ConstantBooleanType(false)
		);
	}

}
