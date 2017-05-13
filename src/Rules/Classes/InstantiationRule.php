<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
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

	public function __construct(Broker $broker, FunctionCallParametersCheck $check)
	{
		$this->broker = $broker;
		$this->check = $check;
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

		if (!$classReflection->hasMethod('__construct') && !$classReflection->hasMethod($class)) {
			if (count($node->args) > 0) {
				return [
					sprintf(
						'Class %s does not have a constructor and must be instantiated without any parameters.',
						$classReflection->getDisplayName()
					),
				];
			}

			return [];
		}

		return $this->check->check(
			$classReflection->hasMethod('__construct') ? $classReflection->getMethod('__construct', $scope) : $classReflection->getMethod($class),
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
		);
	}

}
