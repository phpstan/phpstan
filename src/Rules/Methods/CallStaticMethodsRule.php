<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Node;
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

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Rules\FunctionCallParametersCheck $check
	 */
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
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$methodCall = $node->getParserNode();
		if (!is_string($methodCall->name)) {
			return [];
		}
		$name = $methodCall->name;
		$currentClass = $node->getScope()->getClass();
		if ($currentClass === null) {
			return [];
		}
		$currentClassReflection = $this->broker->getClass($currentClass);
		if (!($methodCall->class instanceof Name)) {
			return [];
		}
		$class = (string) $methodCall->class;
		if ($class === 'self' || $class === 'static') {
			$class = $currentClass;
		}
		if ($class === 'parent') {
			if ($currentClassReflection->getParentClass() === false) {
				return [
					sprintf(
						'%s::%s() calls to parent::%s() but %s does not extend any class.',
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
				if ($name === '__construct' && $currentClassReflection->getParentClass()->hasMethod('__construct')) {
					return $this->check->check(
						$currentClassReflection->getParentClass()->getMethod('__construct'),
						$methodCall,
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
					$name
				),
			];
		}

		if ($currentClass !== null && $method->getDeclaringClass()->getName() !== $currentClass) {
			if (in_array($method->getDeclaringClass()->getName(), $currentClassReflection->getParentClassesNames(), true)) {
				if ($method->isPrivate()) {
					return [
						sprintf(
							'Call to private static method %s() of class %s.',
							$name,
							$method->getDeclaringClass()->getName()
						),
					];
				}
			} else {
				if (!$method->isPublic()) {
					return [
						sprintf(
							'Call to %s static method %s() of class %s.',
							$method->isPrivate() ? 'private' : 'protected',
							$name,
							$method->getDeclaringClass()->getName()
						),
					];
				}
			}
		}

		$methodName = $class . '::' . $name . '()';

		return $this->check->check(
			$method,
			$methodCall,
			[
				'Static method ' . $methodName . ' invoked with %d parameter, %d required.',
				'Static method ' . $methodName . ' invoked with %d parameters, %d required.',
				'Static method ' . $methodName . ' invoked with %d parameter, at least %d required.',
				'Static method ' . $methodName . ' invoked with %d parameters, at least %d required.',
				'Static method ' . $methodName . ' invoked with %d parameter, %d-%d required.',
				'Static method ' . $methodName . ' invoked with %d parameters, %d-%d required.',
			]
		);
	}

}
