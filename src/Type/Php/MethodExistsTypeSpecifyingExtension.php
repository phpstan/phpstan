<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;

class MethodExistsTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var TypeSpecifier */
	private $typeSpecifier;

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function isFunctionSupported(
		FunctionReflection $functionReflection,
		FuncCall $node,
		TypeSpecifierContext $context
	): bool
	{
		return $functionReflection->getName() === 'method_exists'
			&& $context->truthy()
			&& count($node->args) >= 2;
	}

	public function specifyTypes(
		FunctionReflection $functionReflection,
		FuncCall $node,
		Scope $scope,
		TypeSpecifierContext $context
	): SpecifiedTypes
	{
		$methodNameType = $scope->getType($node->args[1]->value);
		if (!$methodNameType instanceof ConstantStringType) {
			return new SpecifiedTypes([], []);
		}
		$methodName = $methodNameType->getValue();

		$exprType = $scope->getType($node->args[0]->value);
		$objectType = new ObjectWithoutClassType();
		$stringType = new StringType();

		if (
			$objectType->isSuperTypeOf($exprType)->no()
			&& $stringType->isSuperTypeOf($exprType)->no()
		) {
			return $this->typeSpecifier->create(
				$node->args[0]->value,
				new NeverType(),
				$context
			);
		}

		if (!$objectType->isSuperTypeOf($exprType)->yes()) {
			return new SpecifiedTypes([], []);
		}

		return $this->typeSpecifier->create(
			$node->args[0]->value,
			new IntersectionType([
				new ObjectWithoutClassType(),
				new HasMethodType($methodName),
			]),
			$context
		);
	}

}
