<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class VersionCompareFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'version_compare';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (count($functionCall->args) < 2) {
			return ParametersAcceptorSelector::selectFromArgs($scope, $functionCall->args, $functionReflection->getVariants())->getReturnType();
		}

		$version1Strings = TypeUtils::getConstantStrings($scope->getType($functionCall->args[0]->value));
		$version2Strings = TypeUtils::getConstantStrings($scope->getType($functionCall->args[1]->value));
		$counts = [
			count($version1Strings),
			count($version2Strings),
		];

		if (isset($functionCall->args[2])) {
			$operatorStrings = TypeUtils::getConstantStrings($scope->getType($functionCall->args[2]->value));
			$counts[] = count($operatorStrings);
			$returnType = new BooleanType();
		} else {
			$returnType = TypeCombinator::union(
				new ConstantIntegerType(-1),
				new ConstantIntegerType(0),
				new ConstantIntegerType(1)
			);
		}

		if (count(array_filter($counts, static function (int $count): bool {
				return $count === 0;
		})) > 0) {
			return $returnType; // one of the arguments is not a constant string
		}

		if (count(array_filter($counts, static function (int $count): bool {
				return $count > 1;
		})) > 1) {
			return $returnType; // more than one argument can have multiple possibilities, avoid combinatorial explosion
		}

		$types = [];
		foreach ($version1Strings as $version1String) {
			foreach ($version2Strings as $version2String) {
				if (isset($operatorStrings)) {
					foreach ($operatorStrings as $operatorString) {
						$value = version_compare($version1String->getValue(), $version2String->getValue(), $operatorString->getValue());
						$types[$value] = new ConstantBooleanType($value);
					}
				} else {
					$value = version_compare($version1String->getValue(), $version2String->getValue());
					$types[$value] = new ConstantIntegerType($value);
				}
			}
		}
		return TypeCombinator::union(...$types);
	}

}
