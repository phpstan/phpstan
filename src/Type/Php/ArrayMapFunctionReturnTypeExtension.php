<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class ArrayMapFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_map';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 2) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$valueType = new MixedType();
		$callableType = $scope->getType($functionCall->args[0]->value);
		if (!$callableType->isCallable()->no()) {
			$valueType = ParametersAcceptorSelector::selectFromArgs(
				$scope,
				$functionCall->args,
				$callableType->getCallableParametersAcceptors($scope)
			)->getReturnType();
		}

		$arrayType = $scope->getType($functionCall->args[1]->value);
		$constantArrays = TypeUtils::getConstantArrays($arrayType);
		if (count($constantArrays) > 0) {
			$arrayTypes = [];
			foreach ($constantArrays as $constantArray) {
				$returnedArrayBuilder = ConstantArrayTypeBuilder::createEmpty();
				foreach ($constantArray->getKeyTypes() as $keyType) {
					$returnedArrayBuilder->setOffsetValueType(
						$keyType,
						$valueType
					);
				}
				$arrayTypes[] = $returnedArrayBuilder->getArray();
			}

			return TypeCombinator::union(...$arrayTypes);
		} elseif ($arrayType instanceof ArrayType) {
			return new ArrayType(
				$arrayType->getIterableKeyType(),
				$valueType
			);
		}

		return new ArrayType(
			new MixedType(),
			$valueType
		);
	}

}
