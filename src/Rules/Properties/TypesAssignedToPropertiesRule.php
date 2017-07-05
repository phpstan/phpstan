<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\RuleLevelHelper;

class TypesAssignedToPropertiesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

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

		/** @var \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch */
		$propertyFetch = $node->var;
		$propertyReflection = $this->findPropertyReflectionFromNode($propertyFetch, $scope);
		if ($propertyReflection === null) {
			return [];
		}

		$propertyType = $propertyReflection->getType();
		$assignedValueType = $scope->getType($node->expr);
		if (!$this->ruleLevelHelper->accepts($propertyType, $assignedValueType)) {
			$propertyDescription = $this->describeProperty($propertyReflection, $propertyFetch);
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
	 * @param \PHPStan\Reflection\PropertyReflection $property
	 * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
	 * @return string|null
	 */
	private function describeProperty(PropertyReflection $property, $propertyFetch)
	{
		if ($propertyFetch instanceof Node\Expr\PropertyFetch) {
			return sprintf('Property %s::$%s', $property->getDeclaringClass()->getDisplayName(), $propertyFetch->name);
		} elseif ($propertyFetch instanceof Node\Expr\StaticPropertyFetch) {
			return sprintf('Static property %s::$%s', $property->getDeclaringClass()->getDisplayName(), $propertyFetch->name);
		}

		return null;
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\PropertyReflection|null
	 */
	private function findPropertyReflectionFromNode($propertyFetch, Scope $scope)
	{
		if ($propertyFetch instanceof Node\Expr\PropertyFetch) {
			if (!is_string($propertyFetch->name)) {
				return null;
			}
			$propertyHolderType = $scope->getType($propertyFetch->var);
			if ($propertyHolderType->getClass() === null) {
				return null;
			}

			return $this->findPropertyReflection($propertyHolderType->getClass(), $propertyFetch->name, $scope);
		} elseif ($propertyFetch instanceof Node\Expr\StaticPropertyFetch) {
			if (
				!($propertyFetch->class instanceof Node\Name)
				|| !is_string($propertyFetch->name)
			) {
				return null;
			}

			return $this->findPropertyReflection($scope->resolveName($propertyFetch->class), $propertyFetch->name, $scope);
		}

		return null;
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\PropertyReflection|null
	 */
	private function findPropertyReflection(string $className, string $propertyName, Scope $scope)
	{
		if (!$this->broker->hasClass($className)) {
			return null;
		}
		$propertyClass = $this->broker->getClass($className);
		if (!$propertyClass->hasProperty($propertyName)) {
			return null;
		}

		return $propertyClass->getProperty($propertyName, $scope);
	}

}
