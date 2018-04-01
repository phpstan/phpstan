<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Analyser\FunctionTypeSpecifyingExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;

class IsNumericFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/**
	 * @var \PHPStan\Analyser\TypeSpecifier
	 */
	private $typeSpecifier;

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): bool
	{
		return strtolower($functionReflection->getName()) === 'is_numeric'
			&& isset($node->args[0])
			&& !$context->null();
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		if ($context->null()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $this->typeSpecifier->create($node->args[0]->value, new UnionType([
			new StringType(),
			new IntegerType(),
			new FloatType(),
		]), $context);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
