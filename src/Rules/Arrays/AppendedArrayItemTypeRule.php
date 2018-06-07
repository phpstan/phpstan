<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Properties\PropertyReflectionFinder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ArrayType;
use PHPStan\Type\VerbosityLevel;

class AppendedArrayItemTypeRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\Properties\PropertyReflectionFinder */
	private $propertyReflectionFinder;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		PropertyReflectionFinder $propertyReflectionFinder,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->propertyReflectionFinder = $propertyReflectionFinder;
		$this->ruleLevelHelper = $ruleLevelHelper;
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
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (
			!$node instanceof Assign
			&& !$node instanceof AssignOp
		) {
			return [];
		}

		if (!($node->var instanceof ArrayDimFetch)) {
			return [];
		}

		if (
			!$node->var->var instanceof \PhpParser\Node\Expr\PropertyFetch
			&& !$node->var->var instanceof \PhpParser\Node\Expr\StaticPropertyFetch
		) {
			return [];
		}

		$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($node->var->var, $scope);
		if ($propertyReflection === null) {
			return [];
		}

		$assignedToType = $propertyReflection->getType();
		if (!($assignedToType instanceof ArrayType)) {
			return [];
		}

		if ($node instanceof Assign) {
			$assignedValueType = $scope->getType($node->expr);
		} else {
			$assignedValueType = $scope->getType($node);
		}

		if (!$this->ruleLevelHelper->accepts($assignedToType->getItemType(), $assignedValueType, $scope->isDeclareStrictTypes())) {
			return [
				sprintf(
					'Array (%s) does not accept %s.',
					$assignedToType->describe(VerbosityLevel::typeOnly()),
					$assignedValueType->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
