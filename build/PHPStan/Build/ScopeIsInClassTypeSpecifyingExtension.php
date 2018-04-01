<?php declare(strict_types = 1);

namespace PHPStan\Build;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\TypeCombinator;

class ScopeIsInClassTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/**
	 * @var string
	 */
	private $isInMethodName;

	/**
	 * @var string
	 */
	private $removeNullMethodName;

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Analyser\TypeSpecifier
	 */
	private $typeSpecifier;

	public function __construct(
		string $isInMethodName,
		string $removeNullMethodName,
		Broker $broker
	)
	{
		$this->isInMethodName = $isInMethodName;
		$this->removeNullMethodName = $removeNullMethodName;
		$this->broker = $broker;
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function getClass(): string
	{
		return Scope::class;
	}

	public function isMethodSupported(
		MethodReflection $methodReflection,
		MethodCall $node,
		TypeSpecifierContext $context
	): bool
	{
		return $methodReflection->getName() === $this->isInMethodName
			&& !$context->null();
	}

	public function specifyTypes(
		MethodReflection $methodReflection,
		MethodCall $node,
		Scope $scope,
		TypeSpecifierContext $context
	): SpecifiedTypes
	{
		$scopeClass = $this->broker->getClass(Scope::class);

		return $this->typeSpecifier->create(
			new MethodCall($node->var, $this->removeNullMethodName),
			TypeCombinator::removeNull(
				$scopeClass->getMethod($this->removeNullMethodName, $scope)->getReturnType()
			),
			$context
		);
	}

}
