<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class GettimeofdayDynamicFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'gettimeofday';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$arrayType = new ConstantArrayType([
			new ConstantStringType('sec'),
			new ConstantStringType('usec'),
			new ConstantStringType('minuteswest'),
			new ConstantStringType('dsttime'),
		], [
			new IntegerType(),
			new IntegerType(),
			new IntegerType(),
			new IntegerType(),
		]);
		$floatType = new FloatType();

		if (!isset($functionCall->args[0])) {
			return $arrayType;
		}

		$argType = $scope->getType($functionCall->args[0]->value);
		$isTrueType = (new ConstantBooleanType(true))->isSuperTypeOf($argType);
		$isFalseType = (new ConstantBooleanType(false))->isSuperTypeOf($argType);
		$compareTypes = $isTrueType->compareTo($isFalseType);
		if ($compareTypes === $isTrueType) {
			return $floatType;
		}
		if ($compareTypes === $isFalseType) {
			return $arrayType;
		}

		if ($argType instanceof MixedType) {
			return new BenevolentUnionType([$arrayType, $floatType]);
		}

		return new UnionType([$arrayType, $floatType]);
	}

}
