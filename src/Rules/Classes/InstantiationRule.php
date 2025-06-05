<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleErrorBuilder;

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
					RuleErrorBuilder::message(sprintf('Using %s outside of class scope.', $class))
						->identifier('instantiation.staticOutsideClass')
						->build(),
				];
			}
			return [];
		} elseif ($class === 'self') {
			if (!$scope->isInClass()) {
				return [
					RuleErrorBuilder::message(sprintf('Using %s outside of class scope.', $class))
						->identifier('instantiation.selfOutsideClass')
						->build(),
				];
			}
			$classReflection = $scope->getClassReflection();
		} elseif ($class === 'parent') {
			if (!$scope->isInClass()) {
				return [
					RuleErrorBuilder::message(sprintf('Using %s outside of class scope.', $class))
						->identifier('instantiation.parentOutsideClass')
						->build(),
				];
			}
			if ($scope->getClassReflection()->getParentClass() === false) {
				return [
					RuleErrorBuilder::message(sprintf(
						'%s::%s() calls new parent but %s does not extend any class.',
						$scope->getClassReflection()->getDisplayName(),
						$scope->getFunctionName(),
						$scope->getClassReflection()->getDisplayName()
					))->identifier('instantiation.parentNoExtends')->build(),
				];
			}
			$classReflection = $scope->getClassReflection()->getParentClass();
		} else {
			if (!$this->broker->hasClass($class)) {
				return [
					RuleErrorBuilder::message(sprintf('Instantiated class %s not found.', $class))
						->identifier('instantiation.classNotFound')
						->build(),
				];
			} else {
				$messages = $this->classCaseSensitivityCheck->checkClassNames([$class]);
			}

			$classReflection = $this->broker->getClass($class);
		}

		if ($classReflection->isInterface()) {
			// Assuming $messages already contains RuleError[] or is empty.
			// If $messages can contain strings from checkClassNames, they'd need conversion.
			$error = RuleErrorBuilder::message(sprintf('Cannot instantiate interface %s.', $classReflection->getDisplayName()))
				->identifier('instantiation.interface')
				->build();
			return array_merge($messages, [$error]);
		}

		if ($classReflection->isAbstract()) {
			$error = RuleErrorBuilder::message(sprintf('Instantiated class %s is abstract.', $classReflection->getDisplayName()))
				->identifier('instantiation.abstractClass')
				->build();
			return array_merge($messages, [$error]);
		}

		if (!$classReflection->hasNativeMethod('__construct') && !$classReflection->hasNativeMethod($class)) {
			if (count($node->args) > 0) {
				$error = RuleErrorBuilder::message(sprintf(
					'Class %s does not have a constructor and must be instantiated without any parameters.',
					$classReflection->getDisplayName()
				))->identifier('instantiation.noConstructorParams')->build();
				return array_merge($messages, [$error]);
			}

			return $messages; // Return earlier messages if any
		}

		return array_merge($messages, $this->check->check(
			$classReflection->hasNativeMethod('__construct') ? $classReflection->getNativeMethod('__construct') : $classReflection->getNativeMethod($class),
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
