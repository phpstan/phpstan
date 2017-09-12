<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
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
		if (!is_string($node->name)) {
			return [];
		}

		$name = $node->name;
		if ($node->class instanceof Name) {
			$class = (string) $node->class;
			if ($class === 'self' || $class === 'static') {
				if (!$scope->isInClass()) {
					return [
						sprintf(
							'Accessing %s::$%s outside of class scope.',
							$class,
							$name
						),
					];
				}
				$classReflection = $scope->getClassReflection();
			} elseif ($class === 'parent') {
				if (!$scope->isInClass()) {
					return [
						sprintf(
							'Accessing %s::$%s outside of class scope.',
							$class,
							$name
						),
					];
				}
				if ($scope->getClassReflection()->getParentClass() === false) {
					return [
						sprintf(
							'%s::%s() accesses parent::$%s but %s does not extend any class.',
							$scope->getClassReflection()->getDisplayName(),
							$scope->getFunctionName(),
							$name,
							$scope->getClassReflection()->getDisplayName()
						),
					];
				}

				if ($scope->getFunctionName() === null) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				$currentMethodReflection = $scope->getClassReflection()->getMethod(
					$scope->getFunctionName(),
					$scope
				);
				if (!$currentMethodReflection->isStatic()) {
					// calling parent::method() from instance method
					return [];
				}

				$classReflection = $scope->getClassReflection()->getParentClass();
			} else {
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
			}
		} else {
			$classType = $scope->getType($node->class);
			if ($classType->getClass() === null) {
				return [];
			}
			if (!$this->broker->hasClass($classType->getClass())) {
				return [
					sprintf(
						'Access to static property $%s on an unknown class %s.',
						$name,
						$classType->getClass()
					),
				];
			}
			$classReflection = $this->broker->getClass($classType->getClass());
		}

		if (!$classReflection->hasProperty($name)) {
			if ($scope->isSpecified($node)) {
				return [];
			}

			return [
				sprintf(
					'Access to an undefined static property %s::$%s.',
					$classReflection->getDisplayName(),
					$name
				),
			];
		}

		$property = $classReflection->getProperty($name, $scope);
		if (!$property->isStatic()) {
			return [
				sprintf(
					'Static access to instance property %s::$%s.',
					$property->getDeclaringClass()->getDisplayName(),
					$name
				),
			];
		}

		if (!$scope->canAccessProperty($property)) {
			return [
				sprintf(
					'Access to %s property $%s of class %s.',
					$property->isPrivate() ? 'private' : 'protected',
					$name,
					$property->getDeclaringClass()->getDisplayName()
				),
			];
		}

		return [];
	}

}
