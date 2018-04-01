<?php declare(strict_types = 1);

namespace PHPStan\Tests;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StaticMethodTypeSpecifyingExtension;

class AssertionClassStaticMethodTypeSpecifyingExtension implements StaticMethodTypeSpecifyingExtension
{

	/** @var bool|null */
	private $nullContext;

	public function __construct(?bool $nullContext)
	{
		$this->nullContext = $nullContext;
	}

	public function getClass(): string
	{
		return AssertionClass::class;
	}

	public function isStaticMethodSupported(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		TypeSpecifierContext $context
	): bool
	{
		if ($this->nullContext === null) {
			return $staticMethodReflection->getName() === 'assertInt';
		}

		if ($this->nullContext) {
			return $staticMethodReflection->getName() === 'assertInt' && $context->null();
		}

		return $staticMethodReflection->getName() === 'assertInt' && !$context->null();
	}

	public function specifyTypes(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		Scope $scope,
		TypeSpecifierContext $context
	): SpecifiedTypes
	{
		return new SpecifiedTypes(['$bar' => [$node->args[0]->value, new IntegerType()]]);
	}

}
