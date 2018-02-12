<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Context;
use PHPStan\Analyser\FunctionTypeSpecifyingExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Reflection\FunctionReflection;

class AssertFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/**
	 * @var \PHPStan\Analyser\TypeSpecifier
	 */
	private $typeSpecifier;

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, Context $context): bool
	{
		return $functionReflection->getName() === 'assert'
			&& isset($node->args[0]);
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, Context $context): SpecifiedTypes
	{
		return $this->typeSpecifier->specifyTypesInCondition($scope, $node->args[0]->value, Context::createTruthy());
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
