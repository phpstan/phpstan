<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class DefaultValueTypesAssignedToPropertiesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return Property::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Property $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($scope->getClass() === null || !$this->broker->hasClass($scope->getClass())) {
			return [];
		}

		$classReflection = $this->broker->getClass($scope->getClass());

		$errors = [];
		foreach ($node->props as $property) {
			if ($property->default === null) {
				continue;
			}

			if ($property->default instanceof Node\Expr\ConstFetch && (string) $property->default->name === 'null') {
				continue;
			}

			$propertyReflection = $classReflection->getProperty($property->name);
			$propertyType = $propertyReflection->getType();
			$defaultValueType = $scope->getType($property->default);
			if ($propertyType->accepts($defaultValueType)) {
				continue;
			}

			$errors[] = sprintf(
				'%s %s::$%s (%s) does not accept default value of type %s.',
				$node->isStatic() ? 'Static property' : 'Property',
				$scope->getClass(),
				$property->name,
				$propertyType->describe(),
				$defaultValueType->describe()
			);
		}

		return $errors;
	}

}
