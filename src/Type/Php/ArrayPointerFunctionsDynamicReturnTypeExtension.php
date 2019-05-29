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

class ArrayPointerFunctionsDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	/** @var string[] */
	private $functions = [
		'reset',
		'end',
		'current',
		'prev',
		'next',
	];

	/** @var string[] */
	private $statefulFunctions = [
		'current',
		'prev',
		'next',
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return in_array($functionReflection->getName(), $this->functions, true);
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
		$iterableAtLeastOnce = $argType->isIterableAtLeastOnce();
		if ($iterableAtLeastOnce->no()) {
			return new ConstantBooleanType(false);
		}

		$constantArrays = TypeUtils::getConstantArrays($argType);
		if (count($constantArrays) > 0) {
			$keyTypes = [];
			foreach ($constantArrays as $constantArray) {
				$arrayKeyTypes = $constantArray->getKeyTypes();
				if (count($arrayKeyTypes) === 0) {
					$keyTypes[] = new ConstantBooleanType(false);
					continue;
				}

				if ($functionReflection->getName() === 'reset') {
					$keyTypes[] = $constantArray->getOffsetValueType($arrayKeyTypes[0]);
				} elseif ($functionReflection->getName() === 'end') {
					$keyTypes[] = $constantArray->getOffsetValueType($arrayKeyTypes[count($arrayKeyTypes) - 1]);
				} else {
					$keyTypes[] = $constantArray->getItemType();
				}
			}

			if (in_array($functionReflection->getName(), $this->statefulFunctions, true)) {
				$keyTypes[] = new ConstantBooleanType(false);
			}

			return TypeCombinator::union(...$keyTypes);
		}

		$itemType = $argType->getIterableValueType();
		if ($iterableAtLeastOnce->yes()) {
			return $itemType;
		}

		return TypeCombinator::union($itemType, new ConstantBooleanType(false));
	}

}
