<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
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
			return TypeCombinator::union($haystackArgType->getIterableKeyType(), new ConstantBooleanType(false), new NullType());
		}

		$haystackIsArray = $this->isArrayType($haystackArgType);
		if ($haystackIsArray->no()) {
			return new NullType();
		}

		$needleArgType = $scope->getType($functionCall->args[0]->value);
		if ($haystackArgType->getIterableValueType()->isSuperTypeOf($needleArgType)->no()) {
			return new ConstantBooleanType(false);
		}

		$types = [];
		if ($haystackIsArray->maybe()) {
			$types[] = new NullType();
		}

		$constantArrays = TypeUtils::getConstantArrays($haystackArgType);
		if (count($constantArrays) > 0) {
			foreach ($constantArrays as $constantArray) {
				$types[] = $this->resolveTypeFromConstantHaystackAndNeedle($needleArgType, $constantArray);
			}

			return TypeCombinator::union(...$types);
		}

		$haystackArrays = $this->pickArrays($haystackArgType);

		$returnType = TypeCombinator::union(
			$haystackArrays->getIterableKeyType(),
			new ConstantBooleanType(false),
			...$types
		);

		return $returnType;
	}

	private function resolveTypeFromConstantHaystackAndNeedle(Type $needle, ConstantArrayType $haystack): Type
	{
		$matchesByType = [];

		foreach ($haystack->getValueTypes() as $index => $valueType) {
			$isNeedleSuperType = $valueType->isSuperTypeOf($needle);
			if ($isNeedleSuperType->no()) {
				$matchesByType[] = new ConstantBooleanType(false);
				continue;
			}

			if ($needle instanceof ConstantScalarType && $valueType instanceof ConstantScalarType
				&& $needle->getValue() === $valueType->getValue()
			) {
				return $haystack->getKeyTypes()[$index];
			}

			$matchesByType[] = $haystack->getKeyTypes()[$index];
			if (!$isNeedleSuperType->maybe()) {
				continue;
			}

			$matchesByType[] = new ConstantBooleanType(false);
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

	private function pickArrays(Type $type): Type
	{
		if ($type instanceof ArrayType) {
			return $type;
		}

		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			$arrayTypes = [];

			foreach ($type->getTypes() as $innerType) {
				if (!($innerType instanceof ArrayType)) {
					continue;
				}

				$arrayTypes[] = $innerType;
			}

			return TypeCombinator::union(...$arrayTypes);
		}

		throw new ShouldNotHappenException();
	}

	private function isArrayType(Type $type): TrinaryLogic
	{
		if ($type instanceof ArrayType) {
			return TrinaryLogic::createYes();
		}

		if ($type instanceof MixedType) {
			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof UnionType || $type instanceof IntersectionType) {
			$matches = TrinaryLogic::createNo();

			foreach ($type->getTypes() as $innerType) {
				$matches = TrinaryLogic::extremeIdentity($matches, $this->isArrayType($innerType));
			}

			return $matches;
		}

		return TrinaryLogic::createNo();
	}

}
