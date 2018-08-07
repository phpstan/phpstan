<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class MbConvertVariablesDynamicFunctionReturnTypeExtension extends MbStringAbstractBaseDynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'mb_convert_variables' && $this->isMbstringExtensionLoaded();
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (count($functionCall->args) < 3) {
			return new NullType();
		}

		$results = array_unique(array_map(function (ConstantStringType $encoding): bool {
			return $this->isSupportedEncoding($encoding->getValue());
		}, TypeUtils::getConstantStrings($scope->getType($functionCall->args[0]->value))));

		if (count($results) !== 1) {
			return new UnionType([new StringType(), new ConstantBooleanType(false)]);
		}

		if ($results[0] === false) {
			return new ConstantBooleanType(false);
		}

		return new UnionType([new StringType(), new ConstantBooleanType(false)]);
	}

}
