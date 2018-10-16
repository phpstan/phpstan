<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class CurlInitReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'curl_init';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		\PhpParser\Node\Expr\FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$argsCount = count($functionCall->args);
		if ($argsCount === 0) {
			return new ResourceType();
		}

		return new UnionType([
			new ResourceType(),
			new ConstantBooleanType(false),
		]);
	}

}
