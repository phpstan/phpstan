<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class MbDetectOrderDynamicFunctionReturnTypeExtension extends MbStringAbstractBaseDynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'mb_detect_order' && $this->isMbstringExtensionLoaded();
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		// It always returns an array if no arguments are passed.
		if (!isset($functionCall->args[0])) {
			return new ArrayType(new IntegerType(), new StringType());
		}

		return new BooleanType();
	}

}
