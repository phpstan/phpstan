<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
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

	/**
	 * @var \PHPStan\Rules\ClassCaseSensitivityCheck
	 */
	private $classCaseSensitivityCheck;

	public function __construct(
		Broker $broker,
		FunctionCallParametersCheck $check,
		RuleLevelHelper $ruleLevelHelper,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck
	)
	{
		$this->broker = $broker;
		$this->check = $check;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
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
		$errors = [];
		if ($class instanceof Name) {
			$className = (string) $class;
			if ($className === 'self' || $className === 'static') {
				if (!$scope->isInClass()) {
					return [
						RuleErrorBuilder::message(sprintf(
							'Calling %s::%s() outside of class scope.',
							$class,
							$methodName
						))->identifier(sprintf('method.staticCall.%sOutsideClass', strtolower($className)))->build(),
					];
				}
				$className = $scope->getClassReflection()->getName();
			} elseif ($className === 'parent') {
				if (!$scope->isInClass()) {
					return [
						RuleErrorBuilder::message(sprintf(
							'Calling %s::%s() outside of class scope.',
							$className,
							$methodName
						))->identifier('method.staticCall.parentOutsideClass')->build(),
					];
				}
				$currentClassReflection = $scope->getClassReflection();
				if ($currentClassReflection->getParentClass() === false) {
					return [
						RuleErrorBuilder::message(sprintf(
							'%s::%s() calls parent::%s() but %s does not extend any class.',
							$scope->getClassReflection()->getDisplayName(),
							$scope->getFunctionName(),
							$methodName,
							$scope->getClassReflection()->getDisplayName()
						))->identifier('method.staticCall.parentNoExtends')->build(),
					];
				}

				if ($scope->getFunctionName() === null) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				$currentMethodReflection = $currentClassReflection->getMethod(
					$scope->getFunctionName(),
					$scope
				);
				if (!$currentMethodReflection->isStatic()) {
					if ($methodName === '__construct' && $currentClassReflection->getParentClass()->hasMethod('__construct')) {
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
								'Parameter #%d %s of parent constructor is passed by reference, so it expects variables only.',
							]
						);
					}

					return []; // Already handled by FunctionCallParametersCheck
				}

				$className = $currentClassReflection->getParentClass()->getName();
			} else {
				if (!$this->broker->hasClass($className)) {
					return [
						RuleErrorBuilder::message(sprintf('Call to static method %s() on an unknown class %s.', $methodName, $className))
							->identifier('method.staticCall.unknownClass')
							->build(),
					];
				} else {
					$errors = $this->classCaseSensitivityCheck->checkClassNames([$className]);
				}

				$className = $this->broker->getClass($className)->getName();
			}

			$classType = new ObjectType($className);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$class,
				sprintf('Call to static method %s() on an unknown class %%s.', $methodName) // Template for RuleLevelHelper
			);
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				return $classTypeResult->getUnknownClassErrors(); // Assumed to return RuleError[] or string[]
			}
		}

		if ($classType instanceof StringType) {
			return $errors; // Return earlier errors if any (e.g. case sensitivity)
		}

		if (!$classType->canCallMethods()) {
			$error = RuleErrorBuilder::message(sprintf('Cannot call static method %s() on %s.', $methodName, $classType->describe()))
				->identifier('method.staticCall.cannotCallOnType')
				->build();
			return array_merge($errors, [$error]);
		}

		if (!$classType->hasMethod($methodName)) {
			$error = RuleErrorBuilder::message(sprintf(
				'Call to an undefined static method %s::%s().',
				$classType->describe(),
				$methodName
			))->identifier('method.staticCall.undefined')->build();
			return array_merge($errors, [$error]);
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
				$error = RuleErrorBuilder::message(sprintf(
					'Static call to instance method %s::%s().',
					$method->getDeclaringClass()->getDisplayName(),
					$method->getName()
				))->identifier('method.staticCall.instanceMethod')->build();
				return array_merge($errors, [$error]);
			}
		}

		if (!$scope->canCallMethod($method)) {
			$error = RuleErrorBuilder::message(sprintf(
				'Call to %s %s %s() of class %s.',
				$method->isPrivate() ? 'private' : 'protected',
				$method->isStatic() ? 'static method' : 'method',
				$method->getName(),
				$method->getDeclaringClass()->getDisplayName()
			))->identifier('method.staticCall.inaccessible')->build();
			return array_merge($errors, [$error]);
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

		$errors = array_merge($errors, $this->check->check(
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
		));

		if ($method->getName() !== $methodName) {
			$errors[] = RuleErrorBuilder::message(sprintf('Call to %s with incorrect case: %s', $lowercasedMethodName, $methodName))
				->identifier('method.staticCall.incorrectCase')
				->build();
		}

		return $errors;
	}

}
