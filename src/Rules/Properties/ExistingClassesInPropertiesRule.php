<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class ExistingClassesInPropertiesRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return PropertyProperty::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\PropertyProperty $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$propertyReflection = $scope->getClassReflection()->getProperty($node->name, $scope);
		$propertyType = $propertyReflection->getType();

		$errors = [];
		foreach ($propertyType->getReferencedClasses() as $referencedClass) {
			if ($this->broker->hasClass($referencedClass)) {
				continue;
			}

			$errors[] = sprintf(
				'Property %s::$%s has unknown class %s as its type.',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$node->name,
				$referencedClass
			);
		}

		return $errors;
	}

}
