<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class ExplodeFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'explode';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		if (count($functionCall->args) < 2) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$delimiterType = $scope->getType($functionCall->args[0]->value);
		$stringType = $scope->getType($functionCall->args[1]->value);

		$nonEmptyStringArrayType = new IntersectionType([
			new ArrayType(new IntegerType(), new StringType()),
			new NonEmptyArrayType(),
		]);

		if ($delimiterType instanceof MixedType) {
			return new BenevolentUnionType([
				$nonEmptyStringArrayType,
				new ConstantBooleanType(false),
			]);
		}

		if (!$delimiterType instanceof ConstantStringType) {
			return new UnionType([
				$nonEmptyStringArrayType,
				new ConstantBooleanType(false),
			]);
		}

		$delimiterValue = $delimiterType->getValue();
		if ($delimiterValue === '') {
			return new ConstantBooleanType(false);
		}

		if (!$stringType instanceof ConstantStringType) {
			return $nonEmptyStringArrayType;
		}

		$explodeResult = explode($delimiterValue, $stringType->getValue());
		if ($explodeResult === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return new ConstantArrayType(
			array_map(
				static function (int $key): ConstantIntegerType {
					return new ConstantIntegerType($key);
				},
				array_keys($explodeResult)
			),
			array_map(
				static function (string $value): ConstantStringType {
					return new ConstantStringType($value);
				},
				$explodeResult
			)
		);
	}

}
