<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\FunctionCallParametersCheck;

class CallStaticMethodsRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\FunctionCallParametersCheck
	 */
	private $check;

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	public function __construct(
		Broker $broker,
		FunctionCallParametersCheck $check,
		bool $checkThisOnly
	)
	{
		$this->broker = $broker;
		$this->check = $check;
		$this->checkThisOnly = $checkThisOnly;
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
		if (!is_string($node->name)) {
			return [];
		}

		if ($node->class instanceof Name) {
			$class = (string) $node->class;
		} elseif (!$this->checkThisOnly) {
			$class = $scope->getType($node->class)->getClass();
			if ($class === null) {
				return [];
			}
		} else {
			return [];
		}

		$name = $node->name;
		if ($class === 'self' || $class === 'static') {
			if (!$scope->isInClass()) {
				return [
					sprintf(
						'Calling %s::%s() outside of class scope.',
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
						'Calling %s::%s() outside of class scope.',
						$class,
						$name
					),
				];
			}
			if ($scope->getClassReflection()->getParentClass() === false) {
				return [
					sprintf(
						'%s::%s() calls parent::%s() but %s does not extend any class.',
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
				if ($name === '__construct' && $scope->getClassReflection()->getParentClass()->hasMethod('__construct')) {
					return $this->check->check(
						$scope->getClassReflection()->getParentClass()->getMethod('__construct', $scope),
						$scope,
						$node,
						[
							'Parent constructor invoked with %d parameter, %d required.',
							'Parent constructor invoked with %d parameters, %d required.',
							'Parent constructor invoked with %d parameter, at least %d required.',
							'Parent constructor invoked with %d parameters, at least %d required.',
							'Parent constructor invoked with %d parameter, %d-%d required.',
							'Parent constructor invoked with %d parameters, %d-%d required.',
							'Parameter #%d %s of parent constructor expects %s, %s given.',
							'', // constructor does not have a return type
							'Parameter #%d %s of parent constructor is passed by reference, so it expects variables only.',
						]
					);
				}

				return [];
			}

			$classReflection = $scope->getClassReflection()->getParentClass();
		} else {
			if (!$this->broker->hasClass($class)) {
				return [
					sprintf(
						'Call to static method %s() on an unknown class %s.',
						$name,
						$class
					),
				];
			}
			$classReflection = $this->broker->getClass($class);
		}

		if (!$classReflection->hasMethod($name)) {
			return [
				sprintf(
					'Call to an undefined static method %s::%s().',
					$classReflection->getDisplayName(),
					$name
				),
			];
		}

		$method = $classReflection->getMethod($name, $scope);
		if (!$method->isStatic()) {
			$function = $scope->getFunction();
			if (
				!$function instanceof MethodReflection
				|| $function->isStatic()
				|| !$scope->isInClass()
				|| (
					$scope->getClassReflection()->getName() !== $classReflection->getName()
					&& !$scope->getClassReflection()->isSubclassOf($classReflection->getName())
				)
			) {
				return [
					sprintf(
						'Static call to instance method %s::%s().',
						$method->getDeclaringClass()->getDisplayName(),
						$method->getName()
					),
				];
			}
		}

		if (!$scope->canCallMethod($method)) {
			return [
				sprintf(
					'Call to %s %s %s() of class %s.',
					$method->isPrivate() ? 'private' : 'protected',
					$method->isStatic() ? 'static method' : 'method',
					$method->getName(),
					$method->getDeclaringClass()->getDisplayName()
				),
			];
		}

		$lowercasedMethodName = sprintf(
			'%s %s',
			$method->isStatic() ? 'static method' : 'method',
			$method->getDeclaringClass()->getDisplayName() . '::' . $method->getName() . '()'
		);
		$methodName = sprintf(
			'%s %s',
			$method->isStatic() ? 'Static method' : 'Method',
			$method->getDeclaringClass()->getDisplayName() . '::' . $method->getName() . '()'
		);

		$errors = $this->check->check(
			$method,
			$scope,
			$node,
			[
				$methodName . ' invoked with %d parameter, %d required.',
				$methodName . ' invoked with %d parameters, %d required.',
				$methodName . ' invoked with %d parameter, at least %d required.',
				$methodName . ' invoked with %d parameters, at least %d required.',
				$methodName . ' invoked with %d parameter, %d-%d required.',
				$methodName . ' invoked with %d parameters, %d-%d required.',
				'Parameter #%d %s of ' . $lowercasedMethodName . ' expects %s, %s given.',
				'Result of ' . $lowercasedMethodName . ' (void) is used.',
				'Parameter #%d %s of ' . $lowercasedMethodName . ' is passed by reference, so it expects variables only.',
			]
		);

		if ($method->getName() !== $name) {
			$errors[] = sprintf('Call to %s with incorrect case: %s', $lowercasedMethodName, $name);
		}

		return $errors;
	}

}
