<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
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

		$keyTypes = [];
		$valueTypes = [];
		foreach ($functionCall->args as $arg) {
			$argType = $scope->getType($arg->value);
			if ($arg->unpack) {
				$argType = $argType->getIterableValueType();
				if ($argType instanceof UnionType) {
					foreach ($argType->getTypes() as $innerType) {
						$argType = $innerType;
					}
				}
			}

			$keyTypes[] = TypeUtils::generalizeType($argType->getIterableKeyType());
			$valueTypes[] = $argType->getIterableValueType();
		}

		return new ArrayType(
			TypeCombinator::union(...$keyTypes),
			TypeCombinator::union(...$valueTypes)
		);
	}

}
