<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\VerbosityLevel;

class TypesAssignedToPropertiesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var \PHPStan\Rules\Properties\PropertyDescriptor */
	private $propertyDescriptor;

	/** @var \PHPStan\Rules\Properties\PropertyReflectionFinder */
	private $propertyReflectionFinder;

	public function __construct(
		RuleLevelHelper $ruleLevelHelper,
		PropertyDescriptor $propertyDescriptor,
		PropertyReflectionFinder $propertyReflectionFinder
	)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->propertyDescriptor = $propertyDescriptor;
		$this->propertyReflectionFinder = $propertyReflectionFinder;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr::class;
	}

	/**
	 * @param \PhpParser\Node\Expr $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (
			!$node instanceof Node\Expr\Assign
			&& !$node instanceof Node\Expr\AssignOp
		) {
			return [];
		}

		if (
			!($node->var instanceof Node\Expr\PropertyFetch)
			&& !($node->var instanceof Node\Expr\StaticPropertyFetch)
		) {
			return [];
		}

		/** @var \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch */
		$propertyFetch = $node->var;
		$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($propertyFetch, $scope);
		if ($propertyReflection === null) {
			return [];
		}

		$propertyType = $propertyReflection->getType();

		if ($node instanceof Node\Expr\Assign) {
			$assignedValueType = $scope->getType($node->expr);
		} else {
			$assignedValueType = $scope->getType($node);
		}
		if (!$this->ruleLevelHelper->accepts($propertyType, $assignedValueType, $scope->isDeclareStrictTypes())) {
			$propertyDescription = $this->propertyDescriptor->describeProperty($propertyReflection, $propertyFetch);

			return [
				sprintf(
					'%s (%s) does not accept %s.',
					$propertyDescription,
					$propertyType->describe(VerbosityLevel::typeOnly()),
					$assignedValueType->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
