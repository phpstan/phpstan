<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

final class ArraySearchFunctionDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_search';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 3) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$strictArgType = $scope->getType($functionCall->args[2]->value);
		$haystackArgType = $scope->getType($functionCall->args[1]->value);
		if (!($strictArgType instanceof ConstantBooleanType) || $strictArgType->getValue() === false) {
			return new UnionType([$haystackArgType->getIterableKeyType(), new ConstantBooleanType(false), new NullType()]);
		}

		$needleArgType = $scope->getType($functionCall->args[0]->value);

		if (count(TypeUtils::getArrays($haystackArgType)) === 0) {
			return new NullType();
		}

		if ($haystackArgType->getIterableValueType()->isSuperTypeOf($needleArgType)->no()) {
			return new ConstantBooleanType(false);
		}

		$constantArrays = TypeUtils::getConstantArrays($haystackArgType);
		if (count($constantArrays) > 0) {
			$types = [];
			foreach ($constantArrays as $constantArray) {
				$types[] = $this->resolveTypeFromConstantHaystackAndNeedle($needleArgType, $constantArray);
			}

			return TypeCombinator::union(...$types);
		}

		return TypeCombinator::union($haystackArgType->getIterableKeyType(), new ConstantBooleanType(false));
	}

	private function resolveTypeFromConstantHaystackAndNeedle(Type $needle, ConstantArrayType $haystack): Type
	{
		$matchesByType = [];

		foreach ($haystack->getValueTypes() as $index => $valueType) {
			if ($needle->isSuperTypeOf($valueType)->no()) {
				$matchesByType[] = new ConstantBooleanType(false);
				continue;
			}

			if ($needle instanceof ConstantScalarType && $valueType instanceof ConstantScalarType
				&& $needle->getValue() === $valueType->getValue()
			) {
				return $haystack->getKeyTypes()[$index];
			}

			$matchesByType[] = $haystack->getKeyTypes()[$index];
		}

		if (count($matchesByType) > 0) {
			if (
				$haystack->getIterableValueType()->accepts($needle, true)->yes()
				&& $needle->isSuperTypeOf(new ObjectWithoutClassType())->no()
			) {
				return TypeCombinator::union(...$matchesByType);
			}

			return TypeCombinator::union(new ConstantBooleanType(false), ...$matchesByType);
		}

		return new ConstantBooleanType(false);
	}

}
