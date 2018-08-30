<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class CallMethodsRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\FunctionCallParametersCheck */
	private $check;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var bool */
	private $checkFunctionNameCase;

	/** @var bool */
	private $reportMagicMethods;

	public function __construct(
		Broker $broker,
		FunctionCallParametersCheck $check,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkFunctionNameCase,
		bool $reportMagicMethods
	)
	{
		$this->broker = $broker;
		$this->check = $check;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkFunctionNameCase = $checkFunctionNameCase;
		$this->reportMagicMethods = $reportMagicMethods;
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
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		$name = $node->name->name;
		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->var,
			sprintf('Call to method %s() on an unknown class %%s.', $name),
			static function (Type $type) use ($name): bool {
				return $type->canCallMethods()->yes() && $type->hasMethod($name);
			}
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}
		if (!$type->canCallMethods()->yes()) {
			return [
				sprintf('Cannot call method %s() on %s.', $name, $type->describe(VerbosityLevel::typeOnly())),
			];
		}

		if (!$type->hasMethod($name)) {
			$directClassNames = $typeResult->getReferencedClasses();
			if (!$this->reportMagicMethods) {
				foreach ($directClassNames as $className) {
					if (!$this->broker->hasClass($className)) {
						continue;
					}

					$classReflection = $this->broker->getClass($className);
					if ($classReflection->hasNativeMethod('__call')) {
						return [];
					}
				}
			}

			if (count($directClassNames) === 1) {
				$referencedClass = $directClassNames[0];
				$methodClassReflection = $this->broker->getClass($referencedClass);
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
			}

			return [
				sprintf(
					'Call to an undefined method %s::%s().',
					$type->describe(VerbosityLevel::typeOnly()),
					$name
				),
			];
		}

		$methodReflection = $type->getMethod($name, $scope);
		$messagesMethodName = $methodReflection->getDeclaringClass()->getDisplayName() . '::' . $methodReflection->getName() . '()';
		$errors = [];
		if (!$scope->canCallMethod($methodReflection)) {
			$errors[] = sprintf(
				'Call to %s method %s() of class %s.',
				$methodReflection->isPrivate() ? 'private' : 'protected',
				$methodReflection->getName(),
				$methodReflection->getDeclaringClass()->getDisplayName()
			);
		}

		$errors = array_merge($errors, $this->check->check(
			ParametersAcceptorSelector::selectFromArgs(
				$scope,
				$node->args,
				$methodReflection->getVariants()
			),
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
		));

		if (
			$this->checkFunctionNameCase
			&& strtolower($methodReflection->getName()) === strtolower($name)
			&& $methodReflection->getName() !== $name
		) {
			$errors[] = sprintf('Call to method %s with incorrect case: %s', $messagesMethodName, $name);
		}

		return $errors;
	}

}
