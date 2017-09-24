<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;

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
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	public function __construct(
		Broker $broker,
		FunctionCallParametersCheck $check,
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
					return [
						sprintf(
							'Calling %s::%s() outside of class scope.',
							$class,
							$methodName
						),
					];
				}
				$className = $scope->getClassReflection()->getName();
			} elseif ($className === 'parent') {
				if (!$scope->isInClass()) {
					return [
						sprintf(
							'Calling %s::%s() outside of class scope.',
							$className,
							$methodName
						),
					];
				}
				$currentClassReflection = $scope->getClassReflection();
				if ($currentClassReflection->getParentClass() === false) {
					return [
						sprintf(
							'%s::%s() calls parent::%s() but %s does not extend any class.',
							$scope->getClassReflection()->getDisplayName(),
							$scope->getFunctionName(),
							$methodName,
							$scope->getClassReflection()->getDisplayName()
						),
					];
				}

				if ($scope->getFunctionName() === null) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				$currentMethodReflection = $currentClassReflection->getExtendedMethod(
					$scope->getFunctionName(),
					$scope
				);
				if (!$currentMethodReflection->isStatic()) {
					if ($methodName === '__construct' && $currentClassReflection->getParentClass()->hasExtendedMethod('__construct')) {
						return $this->check->check(
							$currentClassReflection->getParentClass()->getExtendedMethod('__construct', $scope),
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

				$className = $currentClassReflection->getParentClass()->getName();
			}

			if (!$this->broker->hasClass($className)) {
				return [
					sprintf('Call to static method %s() on an unknown class %s.', $methodName, $className),
				];
			}

			$classType = new ObjectType($className);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$class,
				sprintf('Call to static method %s() on an unknown class %%s.', $methodName)
			);
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				return $classTypeResult->getUnknownClassErrors();
			}
		}

		if (!$classType->canCallMethods()) {
			return [
				sprintf('Cannot call static method %s() on %s.', $methodName, $classType->describe()),
			];
		}

		if (!$classType->hasMethod($methodName)) {
			return [
				sprintf(
					'Call to an undefined static method %s::%s().',
					$classType->describe(),
					$methodName
				),
			];
		}

		$method = $classType->getMethod($methodName, $scope);
		if (!$method->isStatic()) {
			$function = $scope->getFunction();
			if (
				!$function instanceof MethodReflection
				|| $function->isStatic()
				|| !$scope->isInClass()
				|| (
					$classType instanceof TypeWithClassName
					&& $scope->getClassReflection()->getName() !== $classType->getClassName()
					&& !$scope->getClassReflection()->isSubclassOf($classType->getClassName())
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
		$displayMethodName = sprintf(
			'%s %s',
			$method->isStatic() ? 'Static method' : 'Method',
			$method->getDeclaringClass()->getDisplayName() . '::' . $method->getName() . '()'
		);

		$errors = $this->check->check(
			$method,
			$scope,
			$node,
			[
				$displayMethodName . ' invoked with %d parameter, %d required.',
				$displayMethodName . ' invoked with %d parameters, %d required.',
				$displayMethodName . ' invoked with %d parameter, at least %d required.',
				$displayMethodName . ' invoked with %d parameters, at least %d required.',
				$displayMethodName . ' invoked with %d parameter, %d-%d required.',
				$displayMethodName . ' invoked with %d parameters, %d-%d required.',
				'Parameter #%d %s of ' . $lowercasedMethodName . ' expects %s, %s given.',
				'Result of ' . $lowercasedMethodName . ' (void) is used.',
				'Parameter #%d %s of ' . $lowercasedMethodName . ' is passed by reference, so it expects variables only.',
			]
		);

		if ($method->getName() !== $methodName) {
			$errors[] = sprintf('Call to %s with incorrect case: %s', $lowercasedMethodName, $methodName);
		}

		return $errors;
	}

}
