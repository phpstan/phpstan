<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class AccessOwnPropertiesRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 */
	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if ($scope->isInClosureBind()) {
			return [];
		}
		if (!is_string($node->name)) {
			return [];
		}
		$type = $scope->getType($node->var);
		$propertyClass = $type->getClass();
		if ($propertyClass === null || $propertyClass !== $scope->getClass()) {
			return [];
		}

		$name = (string) $node->name;
		$classReflection = $this->broker->getClass($propertyClass);

		if (!$classReflection->hasProperty($name)) {
			$parentClassReflection = $classReflection->getParentClass();
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

		return [];
	}

}
