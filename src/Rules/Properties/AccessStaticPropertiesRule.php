<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;

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
						RuleErrorBuilder::message(sprintf(
							'Accessing %s::$%s outside of class scope.',
							$class,
							$name
						))->identifier(sprintf('property.staticAccess.%sOutsideClass', strtolower($class)))->build(),
					];
				}
				$className = $scope->getClassReflection()->getName();
			} elseif ($class === 'parent') {
				if (!$scope->isInClass()) {
					return [
						RuleErrorBuilder::message(sprintf(
							'Accessing %s::$%s outside of class scope.',
							$class,
							$name
						))->identifier('property.staticAccess.parentOutsideClass')->build(),
					];
				}
				if ($scope->getClassReflection()->getParentClass() === false) {
					return [
						RuleErrorBuilder::message(sprintf(
							'%s::%s() accesses parent::$%s but %s does not extend any class.',
							$scope->getClassReflection()->getDisplayName(),
							$scope->getFunctionName(),
							$name,
							$scope->getClassReflection()->getDisplayName()
						))->identifier('property.staticAccess.parentNoExtends')->build(),
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
						RuleErrorBuilder::message(sprintf(
							'Access to static property $%s on an unknown class %s.',
							$name,
							$class
						))->identifier('property.staticAccess.unknownClass')->build(),
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
				sprintf('Access to static property $%s on an unknown class %%s.', $name) // Template
			);
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				return $classTypeResult->getUnknownClassErrors(); // Assumed
			}
		}

		if ($classType instanceof StringType) {
			return $messages; // Return earlier messages if any
		}

		if (!$classType->canAccessProperties()) {
			$error = RuleErrorBuilder::message(sprintf('Cannot access static property $%s on %s.', $name, $classType->describe()))
				->identifier('property.staticAccess.cannotAccessOnType')
				->build();
			return array_merge($messages, [$error]);
		}

		if (!$classType->hasProperty($name)) {
			if ($scope->isSpecified($node)) {
				return $messages;
			}
			$error = RuleErrorBuilder::message(sprintf(
				'Access to an undefined static property %s::$%s.',
				$classType->describe(),
				$name
			))->identifier('property.staticAccess.undefined')->build();
			return array_merge($messages, [$error]);
		}

		$property = $classType->getProperty($name, $scope);
		if (!$property->isStatic()) {
			$error = RuleErrorBuilder::message(sprintf(
				'Static access to instance property %s::$%s.',
				$property->getDeclaringClass()->getDisplayName(),
				$name
			))->identifier('property.staticAccess.instanceProperty')->build();
			return array_merge($messages, [$error]);
		}

		if (!$scope->canAccessProperty($property)) {
			$error = RuleErrorBuilder::message(sprintf(
				'Access to %s property $%s of class %s.',
				$property->isPrivate() ? 'private' : 'protected',
				$name,
				$property->getDeclaringClass()->getDisplayName()
			))->identifier('property.staticAccess.inaccessible')->build();
			return array_merge($messages, [$error]);
		}

		return $messages;
	}

}
