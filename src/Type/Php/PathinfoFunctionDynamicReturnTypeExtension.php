<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

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

			$dirname = new ConstantStringType('dirname');
			$basename = new ConstantStringType('basename');
			$extension = new ConstantStringType('extension');
			$filename = new ConstantStringType('filename');

			return new UnionType([
				new ConstantArrayType(
					[$dirname, $basename, $filename],
					[$stringType, $stringType, $stringType]
				),
				new ConstantArrayType(
					[$dirname, $basename, $extension, $filename],
					[$stringType, $stringType, $stringType, $stringType]
				),
			]);
		}

		return new StringType();
	}

}
