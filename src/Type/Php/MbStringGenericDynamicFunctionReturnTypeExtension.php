<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class MbStringGenericDynamicFunctionReturnTypeExtension extends MbStringAbstractBaseDynamicFunctionReturnTypeExtension
{

	private const MBSTRING_FUNCTIONS = [
		'mb_check_encoding' 	  => 1,
		'mb_chr'                  => 1,
		'mb_convert_case'         => 2,
		'mb_convert_encoding'     => 1,
		'mb_convert_kana'         => 2,
		'mb_decode_numericentity' => 2,
		'mb_encode_numericentity' => 2,
		'mb_ord'                  => 1,
		'mb_preferred_mime_name'  => 0,
		'mb_scrub'                => 1,
		'mb_strcut'               => 3,
		'mb_strimwidth'           => 4,
		'mb_stripos'              => 3,
		'mb_stristr'              => 3,
		'mb_strlen'               => 1,
		'mb_strpos'               => 3,
		'mb_strrchr'              => 3,
		'mb_strrichr'             => 3,
		'mb_strripos'             => 3,
		'mb_strrpos'              => 3,
		'mb_strstr'               => 3,
		'mb_strtolower'           => 1,
		'mb_strtoupper'           => 1,
		'mb_strwidth'             => 1,
		'mb_substr_count'         => 2,
		'mb_substr'               => 3,
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return isset(self::MBSTRING_FUNCTIONS[$functionReflection->getName()]) && $this->isMbstringExtensionLoaded();
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$position = self::MBSTRING_FUNCTIONS[$functionReflection->getName()];

		if (!isset($functionCall->args[$position])) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$results = array_unique(array_map(function (ConstantStringType $encoding): bool {
			return $this->isSupportedEncoding($encoding->getValue());
		}, TypeUtils::getConstantStrings($scope->getType($functionCall->args[$position]->value))));

		if (count($results) !== 1) {
			$returnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();

			if ($returnType instanceof UnionType || $returnType instanceof BooleanType) {
				return $returnType;
			}

			return new UnionType([$returnType, new ConstantBooleanType(false)]);
		}

		if ($results[0] === false) {
			return new ConstantBooleanType(false);
		}

		return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
	}

}
