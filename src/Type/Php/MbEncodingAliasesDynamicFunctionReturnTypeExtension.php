<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;

class MbEncodingAliasesDynamicFunctionReturnTypeExtension extends MbStringAbstractBaseDynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'mb_encoding_aliases' && $this->isMbstringExtensionLoaded();
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if (!isset($functionCall->args[0])) {
			return new NullType();
		}

		$results = array_unique(array_map(function (ConstantStringType $encoding): bool {
			return $this->isSupportedEncoding($encoding->getValue());
		}, TypeUtils::getConstantStrings($scope->getType($functionCall->args[0]->value))));

		if (count($results) !== 1) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		if ($results[0] === false) {
			return new ConstantBooleanType(false);
		}

		return new ArrayType(new IntegerType(), new StringType());
	}

}
