<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;

class IsSubclassOfFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, TypeSpecifierContext $context): bool
	{
		return strtolower($functionReflection->getName()) === 'is_subclass_of'
			&& count($node->args) >= 2
			&& !$context->null();
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$objectType = $scope->getType($node->args[0]->value);
		$stringType = new StringType();
		if (
			!$objectType instanceof MixedType
			&& !$stringType->isSuperTypeOf($objectType)->no()
		) {
			return new SpecifiedTypes();
		}

		$classType = $scope->getType($node->args[1]->value);
		if (!$classType instanceof ConstantStringType || $classType->getValue() === '') {
			return new SpecifiedTypes();
		}

		return $this->typeSpecifier->specifyTypesInCondition(
			$scope,
			new \PhpParser\Node\Expr\Instanceof_(
				$node->args[0]->value,
				new \PhpParser\Node\Name($classType->getValue())
			),
			$context
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
