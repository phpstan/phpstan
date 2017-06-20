<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type;
use PHPStan\Broker\Broker;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;

class CallMethodsRule implements \PHPStan\Rules\Rule
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
	 * @var bool
	 */
	private $checkThisOnly;

	public function __construct(
		Broker $broker,
		FunctionCallParametersCheck $check,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkThisOnly
	)
	{
		$this->broker = $broker;
		$this->check = $check;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkThisOnly = $checkThisOnly;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\MethodCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		if ($this->checkThisOnly && !$this->ruleLevelHelper->isThis($node->var)) {
			return [];
		}

		$type = $scope->getType($node->var);
		if (!$type->canCallMethods()) {
			return [
				sprintf('Cannot call method %s() on %s.', $node->name, $type->describe()),
			];
		}

		if ($type instanceof Type\UnionType) {
			$errors = [];
			foreach ($type->getTypes() as $t) {
				foreach ($this->checkMethodCall($node, $scope, $t) as $error) {
					$errors[] = sprintf(
						'%s in union type %s.',
						substr($error, 0, -1),
						$type->describe()
					);
				}
			}
			return $errors;
		} else {
			return $this->checkMethodCall($node, $scope, $type);
		}
	}



	private function checkMethodCall(Node $node, Scope $scope, Type\Type $type)
	{
		$methodClass = $type->getClass();
		if ($methodClass === null) {
			return [];
		}

		$name = $node->name;
		if (!$this->broker->hasClass($methodClass)) {
			return [
				sprintf(
					'Call to method %s() on an unknown class %s.',
					$name,
					$methodClass
				),
			];
		}

		$methodClassReflection = $this->broker->getClass($methodClass);
		if (!$methodClassReflection->hasMethod($name)) {
			$parentClassReflection = $methodClassReflection->getParentClass();
			while ($parentClassReflection !== false) {
				if ($parentClassReflection->hasMethod($name)) {
					return [
						sprintf(
							'Call to private method %s() of parent class %s.',
							$parentClassReflection->getMethod($name, $scope)->getName(),
							$parentClassReflection->getDisplayName()
						),
					];
				}

				$parentClassReflection = $parentClassReflection->getParentClass();
			}

			return [
				sprintf(
					'Call to an undefined method %s::%s().',
					$methodClassReflection->getDisplayName(),
					$name
				),
			];
		}

		$methodReflection = $methodClassReflection->getMethod($name, $scope);
		$messagesMethodName = $methodReflection->getDeclaringClass()->getDisplayName() . '::' . $methodReflection->getName() . '()';
		if (!$scope->canCallMethod($methodReflection)) {
			return [
				sprintf(
					'Call to %s method %s() of class %s.',
					$methodReflection->isPrivate() ? 'private' : 'protected',
					$methodReflection->getName(),
					$methodReflection->getDeclaringClass()->getDisplayName()
				),
			];
		}

		$errors = $this->check->check(
			$methodReflection,
			$scope,
			$node,
			[
				'Method ' . $messagesMethodName . ' invoked with %d parameter, %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameters, %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameter, at least %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameters, at least %d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameter, %d-%d required.',
				'Method ' . $messagesMethodName . ' invoked with %d parameters, %d-%d required.',
				'Parameter #%d %s of method ' . $messagesMethodName . ' expects %s, %s given.',
				'Result of method ' . $messagesMethodName . ' (void) is used.',
				'Parameter #%d %s of method ' . $messagesMethodName . ' is passed by reference, so it expects variables only.',
			]
		);

		if (strtolower($methodReflection->getName()) === strtolower($name) && $methodReflection->getName() !== $name) {
			$errors[] = sprintf('Call to method %s with incorrect case: %s', $messagesMethodName, $name);
		}

		return $errors;
	}

}
