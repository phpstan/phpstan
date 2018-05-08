<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class PathinfoFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'pathinfo';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		\PhpParser\Node\Expr\FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$argsCount = count($functionCall->args);
		if ($argsCount === 0) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		} elseif ($argsCount === 1) {
			$stringType = new StringType();

			return new ConstantArrayType([
				new ConstantStringType('dirname'),
				new ConstantStringType('basename'),
				new ConstantStringType('extension'),
				new ConstantStringType('filename'),
			], [
				$stringType,
				$stringType,
				$stringType,
				$stringType,
			]);
		}

		return new StringType();
	}

}
