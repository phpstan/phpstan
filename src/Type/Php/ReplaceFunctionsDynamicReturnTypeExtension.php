<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class ReplaceFunctionsDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return in_array(
			$functionReflection->getName(),
			['preg_replace', 'preg_replace_callback', 'str_replace'],
			true
		);
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (count($functionCall->args) < 3) {
			return $functionReflection->getReturnType();
		}

		$subjectArgumentType = $scope->getType($functionCall->args[2]->value);
		$stringType = new StringType();
		$arrayType = new ArrayType(new MixedType(), new MixedType());
		if ($stringType->isSuperTypeOf($subjectArgumentType)->yes()) {
			return $stringType;
		} elseif ($arrayType->isSuperTypeOf($subjectArgumentType)->yes()) {
			if ($subjectArgumentType instanceof ArrayType) {
				return $subjectArgumentType->generalizeValues();
			}
			return $subjectArgumentType;
		}

		return $functionReflection->getReturnType();
	}

}
