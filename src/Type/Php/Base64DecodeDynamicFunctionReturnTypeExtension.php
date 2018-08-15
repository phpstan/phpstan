<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class Base64DecodeDynamicFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'base64_decode';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (!isset($functionCall->args[1])) {
			return new StringType();
		}

		$argType = $scope->getType($functionCall->args[1]->value);

		if ($argType instanceof MixedType) {
			return new BenevolentUnionType([new StringType(), new ConstantBooleanType(false)]);
		}

		$isTrueType = (new ConstantBooleanType(true))->isSuperTypeOf($argType);
		$isFalseType = (new ConstantBooleanType(false))->isSuperTypeOf($argType);
		$compareTypes = $isTrueType->compareTo($isFalseType);
		if ($compareTypes === $isTrueType) {
			return new UnionType([new StringType(), new ConstantBooleanType(false)]);
		}
		if ($compareTypes === $isFalseType) {
			return new StringType();
		}

		// second argument could be interpreted as true
		return new UnionType([new StringType(), new ConstantBooleanType(false)]);
	}

}
