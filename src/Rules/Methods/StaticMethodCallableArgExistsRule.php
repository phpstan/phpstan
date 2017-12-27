<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\CallableExistsCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\CallableType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;

class StaticMethodCallableArgExistsRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\CallableExistsCheck
	 */
	private $check;

	/**
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	public function __construct(
		Broker $broker,
		CallableExistsCheck $check,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->broker = $broker;
		$this->check = $check;
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return StaticCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\StaticCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$methodName = $node->name;
		if (!is_string($methodName)) {
			return [];
		}

		$class = $node->class;
		if ($class instanceof Name) {
			$className = (string) $class;
			if ($className === 'self' || $className === 'static') {
				if (!$scope->isInClass()) {
					return [];
				}
				$className = $scope->getClassReflection()->getName();
			} elseif ($className === 'parent') {
				if (!$scope->isInClass()) {
					return [];
				}
				$currentClassReflection = $scope->getClassReflection();
				if ($currentClassReflection->getParentClass() === false) {
					return [];
				}

				if ($scope->getFunctionName() === null) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				$className = $currentClassReflection->getParentClass()->getName();
			} else {
				$className = $this->broker->getClass($className)->getName();
			}

			$classType = new ObjectType($className);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck($scope, $class, '');
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				return $classTypeResult->getUnknownClassErrors();
			}
		}

		$methodReflection = $classType->getMethod($methodName, $scope);
		$parameters = $methodReflection->getParameters();

		$errors = [];

		foreach ($node->args as $i => $argument) {
			if (!$parameters[$i]->getType() instanceof CallableType) {
				continue;
			}

			$messagePrefix = sprintf('Argument #%d %s of %s should be callable, but passed ', $i + 1, $parameters[$i]->getName(), $node->name);
			$errors = array_merge($errors, $this->check->checkCallableArgument($argument, $scope, $messagePrefix));
		}

		return $errors;
	}

}
