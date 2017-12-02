<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeCombinator;

class AccessStaticPropertiesRule implements \PHPStan\Rules\Rule
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
	 * @var \PHPStan\Rules\ClassCaseSensitivityCheck
	 */
	private $classCaseSensitivityCheck;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
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
		$messages = [];
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
				$className = $scope->getClassReflection()->getName();
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

				$currentMethodReflection = $scope->getClassReflection()->getNativeMethod($scope->getFunctionName());
				if (!$currentMethodReflection->isStatic()) {
					// calling parent::method() from instance method
					return [];
				}

				$className = $scope->getClassReflection()->getParentClass()->getName();
			} else {
				if (!$this->broker->hasClass($class)) {
					return [
						sprintf(
							'Access to static property $%s on an unknown class %s.',
							$name,
							$class
						),
					];
				} else {
					$messages = $this->classCaseSensitivityCheck->checkClassNames([$class]);
				}
				$className = $this->broker->getClass($class)->getName();
			}

			$classType = new ObjectType($className);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$node->class,
				sprintf('Access to static property $%s on an unknown class %%s.', $name)
			);
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				return $classTypeResult->getUnknownClassErrors();
			}
		}

		if ($classType instanceof StringType) {
			return [];
		}

		$typeForDescribe = $classType;
		$classType = TypeCombinator::remove($classType, new StringType());

		if (!$classType->canAccessProperties()) {
			return array_merge($messages, [
				sprintf('Cannot access static property $%s on %s.', $name, $typeForDescribe->describe()),
			]);
		}

		if (!$classType->hasProperty($name)) {
			if ($scope->isSpecified($node)) {
				return $messages;
			}

			return array_merge($messages, [
				sprintf(
					'Access to an undefined static property %s::$%s.',
					$typeForDescribe->describe(),
					$name
				),
			]);
		}

		$property = $classType->getProperty($name, $scope);
		if (!$property->isStatic()) {
			return array_merge($messages, [
				sprintf(
					'Static access to instance property %s::$%s.',
					$property->getDeclaringClass()->getDisplayName(),
					$name
				),
			]);
		}

		if (!$scope->canAccessProperty($property)) {
			return array_merge($messages, [
				sprintf(
					'Access to %s property $%s of class %s.',
					$property->isPrivate() ? 'private' : 'protected',
					$name,
					$property->getDeclaringClass()->getDisplayName()
				),
			]);
		}

		return $messages;
	}

}
