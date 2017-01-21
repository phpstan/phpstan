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

	public function __construct(Broker $broker, FunctionCallParametersCheck $check)
	{
		$this->broker = $broker;
		$this->check = $check;
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

		$name = $node->name;
		$currentClass = $scope->getClass();
		if ($currentClass === null) {
			return [];
		}

		$currentClassReflection = $this->broker->getClass($currentClass);
		if (!($node->class instanceof Name)) {
			return [];
		}

		$class = (string) $node->class;
		if ($class === 'self' || $class === 'static') {
			$class = $currentClass;
		}

		if ($class === 'parent') {
			if ($currentClassReflection->getParentClass() === false) {
				return [
					sprintf(
						'%s::%s() calls to parent::%s() but %s does not extend any class.',
						$currentClass,
						$scope->getFunctionName(),
						$name,
						$currentClass
					),
				];
			}

			$currentMethodReflection = $currentClassReflection->getMethod(
				$scope->getFunctionName(),
				$scope
			);
			if (!$currentMethodReflection->isStatic()) {
				if ($name === '__construct' && $currentClassReflection->getParentClass()->hasMethod('__construct')) {
					return $this->check->check(
						$currentClassReflection->getParentClass()->getMethod('__construct', $scope),
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
						]
					);
				}

				return [];
			}

			$class = $currentClassReflection->getParentClass()->getName();
		}

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
		if (!$classReflection->hasMethod($name)) {
			return [
				sprintf(
					'Call to an undefined static method %s::%s().',
					$classReflection->getName(),
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
				|| (
					$scope->getClass() !== $class
					&& !$currentClassReflection->isSubclassOf($class)
				)
			) {
				return [
					sprintf(
						'Static call to instance method %s::%s().',
						$method->getDeclaringClass()->getName(),
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
					$method->getDeclaringClass()->getName()
				),
			];
		}

		$lowercasedMethodName = sprintf(
			'%s %s',
			$method->isStatic() ? 'static method' : 'method',
			$method->getDeclaringClass()->getName() . '::' . $method->getName() . '()'
		);
		$methodName = sprintf(
			'%s %s',
			$method->isStatic() ? 'Static method' : 'Method',
			$method->getDeclaringClass()->getName() . '::' . $method->getName() . '()'
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
			]
		);

		if ($method->getName() !== $name) {
			$errors[] = sprintf('Call to %s with incorrect case: %s', $lowercasedMethodName, $name);
		}

		return $errors;
	}

}
