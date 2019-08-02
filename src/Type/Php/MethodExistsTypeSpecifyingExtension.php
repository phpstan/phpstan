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
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

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
		$methodNames = $this->getMethodNames($methodNameType);
		if (count($methodNames) === 0) {
			return new SpecifiedTypes([], []);
		}

		return $this->typeSpecifier->create(
			$node->args[0]->value,
			new IntersectionType([
				new ObjectWithoutClassType(),
				TypeCombinator::union(...array_map(
					static function (string $methodName): HasMethodType {
						return new HasMethodType($methodName);
					},
					$methodNames
				)),
			]),
			$context
		);
	}

	/**
	 * @return string[]
	 */
	private function getMethodNames(Type $methodNameType): array
	{
		if ($methodNameType instanceof ConstantStringType) {
			return [$methodNameType->getValue()];
		}

		if (!$methodNameType instanceof UnionType) {
			return [];
		}

		$methodNames = [];
		foreach ($methodNameType->getTypes() as $subType) {
			if (!$subType instanceof ConstantStringType) {
				return [];
			}

			$methodNames[] = $subType->getValue();
		}

		return $methodNames;
	}

}
