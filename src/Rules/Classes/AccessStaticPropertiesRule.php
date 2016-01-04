<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Analyser\Node;
use PHPStan\Broker\Broker;

class AccessStaticPropertiesRule implements \PHPStan\Rules\Rule
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
		return StaticPropertyFetch::class;
	}

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$propertyFetch = $node->getParserNode();
		$name = (string) $propertyFetch->name;
		$currentClass = $node->getScope()->getClass();
		if ($currentClass === null) {
			return [];
		}
		$currentClassReflection = $this->broker->getClass($currentClass);
		$class = (string) $propertyFetch->class;
		if ($class === 'self' || $class === 'static') {
			$class = $currentClass;
		}
		if ($class === 'parent') {
			if ($currentClassReflection->getParentClass() === false) {
				return [
					sprintf(
						'%s::%s() accesses parent::$%s but %s does not extend any class.',
						$currentClass,
						$node->getScope()->getFunction(),
						$name,
						$currentClass
					),
				];
			}

			$currentMethodReflection = $currentClassReflection->getMethod(
				$node->getScope()->getFunction()
			);
			if (!$currentMethodReflection->isStatic()) {
				// calling parent::method() from instance method
				return [];
			}

			$class = $currentClassReflection->getParentClass()->getName();
		}

		$classReflection = $this->broker->getClass($class);
		if (!$classReflection->hasProperty($name)) {
			return [
				sprintf(
					'Access to an undefined static property %s::$%s.',
					$class,
					$name
				),
			];
		}

		$property = $classReflection->getProperty($name);
		if (!$property->isStatic()) {
			return [
				sprintf(
					'Static access to instance property %s::$%s.',
					$class,
					$name
				),
			];
		}

		if ($currentClass !== null && $property->getDeclaringClass()->getName() !== $currentClass) {
			if (in_array($class, $currentClassReflection->getParentClassesNames(), true)) {
				if ($property->isPrivate()) {
					return [
						sprintf(
							'Access to private static property $%s of class %s.',
							$name,
							$property->getDeclaringClass()->getName()
						),
					];
				}
			} else {
				if (!$property->isPublic()) {
					return [
						sprintf(
							'Access to %s static property $%s of class %s.',
							$property->isPrivate() ? 'private' : 'protected',
							$name,
							$property->getDeclaringClass()->getName()
						),
					];
				}
			}
		}

		return [];
	}

}
