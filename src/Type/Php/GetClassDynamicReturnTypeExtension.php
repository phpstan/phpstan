<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

class GetClassDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'get_class';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$args = $functionCall->args;
		if (count($args) === 0) {
			if ($scope->isInClass()) {
				return new ConstantStringType($scope->getClassReflection()->getName());
			}

			return new ConstantBooleanType(false);
		}

		$argType = $scope->getType($args[0]->value);
		$classNames = TypeUtils::getDirectClassNames($argType);
		if (count($classNames) === 0) {
			return new ClassStringType();
		}

		$types = [];
		foreach ($classNames as $className) {
			$types[] = new GenericClassStringType(new ObjectType($className));
		}

		return TypeCombinator::union(...$types);
	}

}
