<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class AccessStaticPropertiesRule implements \PHPStan\Rules\Rule
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
		return StaticPropertyFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\StaticPropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name) || !($node->class instanceof Node\Name)) {
			return [];
		}

		$name = $node->name;
		$currentClass = $scope->getClass();
		if ($currentClass === null) {
			return [];
		}

		$currentClassReflection = $this->broker->getClass($currentClass);
		$class = (string) $node->class;
		if ($class === 'self' || $class === 'static') {
			$class = $currentClass;
		}

		if ($class === 'parent') {
			if ($currentClassReflection->getParentClass() === false) {
				return [
					sprintf(
						'%s::%s() accesses parent::$%s but %s does not extend any class.',
						$currentClass,
						$scope->getFunctionName(),
						$name,
						$currentClass
					),
				];
			}

			$currentMethodReflection = $currentClassReflection->getMethod(
				$scope->getFunctionName()
			);
			if (!$currentMethodReflection->isStatic()) {
				// calling parent::method() from instance method
				return [];
			}

			$class = $currentClassReflection->getParentClass()->getName();
		}

		if (!$this->broker->hasClass($class)) {
			return [
				sprintf(
					'Access to static property $%s on an unknown class %s.',
					$name,
					$class
				),
			];
		}

		$classReflection = $this->broker->getClass($class);
		if (!$classReflection->hasProperty($name)) {
			if ($scope->isSpecified($node)) {
				return [];
			}

			return [
				sprintf(
					'Access to an undefined static property %s::$%s.',
					$classReflection->getName(),
					$name
				),
			];
		}

		$property = $classReflection->getProperty($name, $scope);
		if (!$property->isStatic()) {
			return [
				sprintf(
					'Static access to instance property %s::$%s.',
					$property->getDeclaringClass()->getName(),
					$name
				),
			];
		}

		if (!$scope->canAccessProperty($property)) {
			return [
				sprintf(
					'Cannot access property %s::$%s from current scope.',
					$property->getDeclaringClass()->getName(),
					$name
				),
			];
		}

		return [];
	}

}
