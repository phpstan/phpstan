<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class ArrayKeyLastDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_key_last';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (!isset($functionCall->args[0])) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$argType = $scope->getType($functionCall->args[0]->value);
		$iterableAtLeastOnce = $argType->isIterableAtLeastOnce();
		if ($iterableAtLeastOnce->no()) {
			return new NullType();
		}

		$constantArrays = TypeUtils::getConstantArrays($argType);
		if (count($constantArrays) > 0) {
			$keyTypes = [];
			foreach ($constantArrays as $constantArray) {
				$arrayKeyTypes = $constantArray->getKeyTypes();
				if (count($arrayKeyTypes) === 0) {
					$keyTypes[] = new NullType();
					continue;
				}

				$keyTypes[] = $arrayKeyTypes[count($arrayKeyTypes) - 1];
			}

			return TypeCombinator::union(...$keyTypes);
		}

		$keyType = $argType->getIterableKeyType();
		if ($iterableAtLeastOnce->yes()) {
			return $keyType;
		}

		return TypeCombinator::union($keyType, new NullType());
	}

}
