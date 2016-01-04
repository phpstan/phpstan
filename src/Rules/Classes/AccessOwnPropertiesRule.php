<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Node;
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
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$propertyFetch = $node->getParserNode();
		if ($node->getScope()->isInClosureBind()) {
			return [];
		}
		if (!($propertyFetch->var instanceof Variable)) {
			return [];
		}
		if ((string) $propertyFetch->var->name !== 'this') {
			return [];
		}
		if (!is_string($propertyFetch->name)) {
			return [];
		}
		$name = (string) $propertyFetch->name;
		$class = $node->getScope()->getClass();
		if ($class === null) {
			return array();
		}
		$classReflection = $this->broker->getClass($class);

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
					$class,
					$name
				),
			];
		}

		return [];
	}

}
