<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;

class TypesAssignedToPropertiesRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Assign::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Assign $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (
			!($node->var instanceof Node\Expr\PropertyFetch)
			&& !($node->var instanceof Node\Expr\StaticPropertyFetch)
		) {
			return [];
		}

		$propertyType = $scope->getType($node->var);
		$assignedValueType = $scope->getType($node->expr);

		if (!$propertyType->accepts($assignedValueType)) {
			$propertyDescription = $this->describeProperty($node->var, $scope);
			if ($propertyDescription === null) {
				return [];
			}

			return [
				sprintf(
					'%s (%s) does not accept %s.',
					$propertyDescription,
					$propertyType->describe(),
					$assignedValueType->describe()
				),
			];
		}

		return [];
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string|null
	 */
	private function describeProperty($propertyFetch, Scope $scope)
	{
		if ($propertyFetch instanceof Node\Expr\PropertyFetch) {
			if (!is_string($propertyFetch->name)) {
				return null;
			}
			$propertyHolderType = $scope->getType($propertyFetch->var);
			if ($propertyHolderType->getClass() === null) {
				return null;
			}

			return sprintf('Property %s::$%s', $propertyHolderType->getClass(), $propertyFetch->name);
		} elseif ($propertyFetch instanceof Node\Expr\StaticPropertyFetch) {
			if (
				!($propertyFetch->class instanceof Node\Name)
				|| !is_string($propertyFetch->name)
			) {
				return null;
			}

			return sprintf('Static property %s::$%s', $scope->resolveName($propertyFetch->class), $propertyFetch->name);
		}

		return null;
	}

}
