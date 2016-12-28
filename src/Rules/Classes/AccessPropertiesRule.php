<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleLevelHelper;

class AccessPropertiesRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkThisOnly
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkThisOnly = $checkThisOnly;
	}

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		if ($this->checkThisOnly && !$this->ruleLevelHelper->isThis($node->var)) {
			return [];
		}

		$type = $scope->getType($node->var);
		if (!$type->canAccessProperties()) {
			return [
				sprintf('Cannot access property $%s on %s.', $node->name, $type->describe()),
			];
		}
		$propertyClass = $type->getClass();
		if ($propertyClass === null) {
			return [];
		}

		$name = $node->name;
		if (!$this->broker->hasClass($propertyClass)) {
			return [
				sprintf(
					'Access to property $%s on an unknown class %s.',
					$name,
					$propertyClass
				),
			];
		}

		$propertyClassReflection = $this->broker->getClass($propertyClass);

		if (!$propertyClassReflection->hasProperty($name)) {
			if ($scope->isSpecified($node)) {
				return [];
			}

			$parentClassReflection = $propertyClassReflection->getParentClass();
			while ($parentClassReflection !== false) {
				if ($parentClassReflection->hasProperty($name)) {
					return [
						sprintf(
							'Access to private property $%s of parent class %s.',
							$name,
							$parentClassReflection->getName()
						),
					];
				}

				$parentClassReflection = $parentClassReflection->getParentClass();
			}

			return [
				sprintf(
					'Access to an undefined property %s::$%s.',
					$propertyClass,
					$name
				),
			];
		}

		$propertyReflection = $propertyClassReflection->getProperty($name, $scope);
		if (!$scope->canAccessProperty($propertyReflection)) {
			return [
				sprintf(
					'Cannot access property %s::$%s from current scope.',
					$propertyReflection->getDeclaringClass()->getName(),
					$name
				),
			];
		}

		return [];
	}

}
