<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\FunctionCallParametersCheck;

class InstantiationRule implements \PHPStan\Rules\Rule
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
	 * @var \PHPStan\Rules\ClassCaseSensitivityCheck
	 */
	private $classCaseSensitivityCheck;

	public function __construct(
		Broker $broker,
		FunctionCallParametersCheck $check,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck
	)
	{
		$this->broker = $broker;
		$this->check = $check;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
	}

	public function getNodeType(): string
	{
		return New_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\New_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!($node->class instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$class = (string) $node->class;
		$messages = [];
		if ($class === 'static') {
			if (!$scope->isInClass()) {
				return [
					sprintf('Using %s outside of class scope.', $class),
				];
			}
			return [];
		} elseif ($class === 'self') {
			if (!$scope->isInClass()) {
				return [
					sprintf('Using %s outside of class scope.', $class),
				];
			}
			$classReflection = $scope->getClassReflection();
		} elseif ($class === 'parent') {
			if (!$scope->isInClass()) {
				return [
					sprintf('Using %s outside of class scope.', $class),
				];
			}
			if ($scope->getClassReflection()->getParentClass() === false) {
				return [
					sprintf(
						'%s::%s() calls new parent but %s does not extend any class.',
						$scope->getClassReflection()->getDisplayName(),
						$scope->getFunctionName(),
						$scope->getClassReflection()->getDisplayName()
					),
				];
			}
			$classReflection = $scope->getClassReflection()->getParentClass();
		} else {
			if (!$this->broker->hasClass($class)) {
				return [
					sprintf('Instantiated class %s not found.', $class),
				];
			} else {
				$messages = $this->classCaseSensitivityCheck->checkClassNames([$class]);
			}

			$classReflection = $this->broker->getClass($class);
		}

		if ($classReflection->isInterface()) {
			return [
				sprintf('Cannot instantiate interface %s.', $classReflection->getDisplayName()),
			];
		}

		if ($classReflection->isAbstract()) {
			return [
				sprintf('Instantiated class %s is abstract.', $classReflection->getDisplayName()),
			];
		}

		if (!$classReflection->hasNativeMethod('__construct') && !$classReflection->hasNativeMethod($class)) {
			if (count($node->args) > 0) {
				return array_merge($messages, [
					sprintf(
						'Class %s does not have a constructor and must be instantiated without any parameters.',
						$classReflection->getDisplayName()
					),
				]);
			}

			return [];
		}

		$constructorReflection = $classReflection->hasNativeMethod('__construct') ? $classReflection->getNativeMethod('__construct') : $classReflection->getNativeMethod($class);
		if (!$scope->canCallMethod($constructorReflection)) {
			$messages[] = sprintf(
				'Cannot instantiate class %s via %s constructor %s::%s().',
				$classReflection->getDisplayName(),
				$constructorReflection->isPrivate() ? 'private' : 'protected',
				$constructorReflection->getDeclaringClass()->getDisplayName(),
				$constructorReflection->getName()
			);
		}

		return array_merge($messages, $this->check->check(
			$constructorReflection,
			$scope,
			$node,
			[
				'Class ' . $classReflection->getDisplayName() . ' constructor invoked with %d parameter, %d required.',
				'Class ' . $classReflection->getDisplayName() . ' constructor invoked with %d parameters, %d required.',
				'Class ' . $classReflection->getDisplayName() . ' constructor invoked with %d parameter, at least %d required.',
				'Class ' . $classReflection->getDisplayName() . ' constructor invoked with %d parameters, at least %d required.',
				'Class ' . $classReflection->getDisplayName() . ' constructor invoked with %d parameter, %d-%d required.',
				'Class ' . $classReflection->getDisplayName() . ' constructor invoked with %d parameters, %d-%d required.',
				'Parameter #%d %s of class ' . $classReflection->getDisplayName() . ' constructor expects %s, %s given.',
				'', // constructor does not have a return type
				'Parameter #%d %s of class ' . $classReflection->getDisplayName() . ' constructor is passed by reference, so it expects variables only',
			]
		));
	}

}
