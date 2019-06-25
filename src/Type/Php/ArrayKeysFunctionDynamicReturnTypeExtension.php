<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class ArrayKeysFunctionDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'array_keys';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$arrayArg = $functionCall->args[0]->value ?? null;
		if ($arrayArg !== null) {
			$valueType = $scope->getType($arrayArg);
			if ($valueType->isArray()->yes()) {
				if ($valueType instanceof ConstantArrayType) {
					return $valueType->getKeysArray();
				}
				$keyType = $valueType->getIterableKeyType();
				return TypeCombinator::intersect(new ArrayType(new IntegerType(), $keyType), ...TypeUtils::getAccessoryTypes($valueType));
			}
		}

		return new ArrayType(
			new IntegerType(),
			new UnionType([new StringType(), new IntegerType()])
		);
	}

}
