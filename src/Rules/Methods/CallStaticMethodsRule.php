<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
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
				$scope->getFunctionName()
			);
			if (!$currentMethodReflection->isStatic()) {
				if ($name === '__construct' && $currentClassReflection->getParentClass()->hasMethod('__construct')) {
					return $this->check->check(
						$currentClassReflection->getParentClass()->getMethod('__construct'),
						$node,
						[
							'Parent constructor invoked with %d parameter, %d required.',
							'Parent constructor invoked with %d parameters, %d required.',
							'Parent constructor invoked with %d parameter, at least %d required.',
							'Parent constructor invoked with %d parameters, at least %d required.',
							'Parent constructor invoked with %d parameter, %d-%d required.',
							'Parent constructor invoked with %d parameters, %d-%d required.',
						]
					);
				}

				return [];
			}

			$class = $currentClassReflection->getParentClass()->getName();
		}

		$classReflection = $this->broker->getClass($class);
		if (!$classReflection->hasMethod($name)) {
			return [
				sprintf(
					'Call to an undefined static method %s::%s().',
					$class,
					$name
				),
			];
		}

		$method = $classReflection->getMethod($name);
		if (!$method->isStatic()) {
			return [
				sprintf(
					'Static call to instance method %s::%s().',
					$class,
					$method->getName()
				),
			];
		}

		if (!$scope->canCallMethod($method)) {
			return [
				sprintf(
					'Call to %s static method %s() of class %s.',
					$method->isPrivate() ? 'private' : 'protected',
					$method->getName(),
					$method->getDeclaringClass()->getName()
				),
			];
		}

		$methodName = $class . '::' . $method->getName() . '()';

		$errors = $this->check->check(
			$method,
			$node,
			[
				'Static method ' . $methodName . ' invoked with %d parameter, %d required.',
				'Static method ' . $methodName . ' invoked with %d parameters, %d required.',
				'Static method ' . $methodName . ' invoked with %d parameter, at least %d required.',
				'Static method ' . $methodName . ' invoked with %d parameters, at least %d required.',
				'Static method ' . $methodName . ' invoked with %d parameter, %d-%d required.',
				'Static method ' . $methodName . ' invoked with %d parameters, %d-%d required.',
			]
		);

		if ($method->getName() !== $name) {
			$errors[] = sprintf('Call to static method %s with incorrect case: %s', $methodName, $name);
		}

		return $errors;
	}

}
