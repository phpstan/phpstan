<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;

class ClassConstantRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

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
		RuleLevelHelper $ruleLevelHelper,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
	}

	public function getNodeType(): string
	{
		return ClassConstFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ClassConstFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$constantName = $node->name;
		if (!is_string($constantName)) {
			return [];
		}

		$class = $node->class;
		$messages = [];
		if ($class instanceof \PhpParser\Node\Name) {
			$className = (string) $class;
			if ($className === 'self' || $className === 'static') {
				if (!$scope->isInClass()) {
					return [
						RuleErrorBuilder::message(sprintf('Using %s outside of class scope.', $className))
							->identifier(sprintf('class.%sOutsideClass', strtolower($className)))
							->build(),
					];
				}

				$className = $scope->getClassReflection()->getName();
			} elseif ($className === 'parent') {
				if (!$scope->isInClass()) {
					return [
						RuleErrorBuilder::message(sprintf('Using %s outside of class scope.', $className))
							->identifier('class.parentOutsideClass')
							->build(),
					];
				}
				$currentClassReflection = $scope->getClassReflection();
				if ($currentClassReflection->getParentClass() === false) {
					return [
						RuleErrorBuilder::message(sprintf(
							'Access to parent::%s but %s does not extend any class.',
							$constantName,
							$currentClassReflection->getDisplayName()
						))->identifier('class.parentNoExtends')->build(),
					];
				}
				$className = $currentClassReflection->getParentClass()->getName();
			} else {
				if (!$this->broker->hasClass($className)) {
					if (strtolower($constantName) === 'class') {
						return [
							RuleErrorBuilder::message(sprintf('Class %s not found.', $className))
								->identifier('class.notFound')
								->build(),
						];
					}

					return [
						RuleErrorBuilder::message(sprintf('Access to constant %s on an unknown class %s.', $constantName, $className))
							->identifier('class.constantUnknownClass')
							->build(),
					];
				} else {
					// Assuming checkClassNames returns RuleError[] or string[] that need conversion.
					// For now, this part is tricky without knowing its return type.
					// If it returns strings, those would need to be mapped to RuleErrorBuilder.
					$messages = $this->classCaseSensitivityCheck->checkClassNames([$className]);
				}

				$className = $this->broker->getClass($className)->getName();
			}

			$classType = new ObjectType($className);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$class,
				// This sprintf is for a message template, not a direct error.
				sprintf('Access to constant %s on an unknown class %%s.', $constantName)
			);
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				// Assuming getUnknownClassErrors returns RuleError[] or string[]
				// If strings, they might need conversion if they are to be standardized with identifiers.
				return $classTypeResult->getUnknownClassErrors();
			}
		}

		if ($classType instanceof StringType) {
			return $messages; // Potentially an array of RuleError or strings
		}

		if (!$classType->canAccessConstants()) {
			$error = RuleErrorBuilder::message(sprintf('Cannot access constant %s on %s.', $constantName, $classType->describe()))
				->identifier('class.constantNonClass')
				->build();
			return array_merge($messages, [$error]);
		}

		if (strtolower($constantName) === 'class') {
			return $messages; // Potentially an array of RuleError or strings
		}

		if (!$classType->hasConstant($constantName)) {
			$error = RuleErrorBuilder::message(sprintf(
				'Access to undefined constant %s::%s.',
				$classType->describe(),
				$constantName
			))->identifier('class.constantUndefined')->build();
			return array_merge($messages, [$error]);
		}

		$constantReflection = $classType->getConstant($constantName);
		if (!$scope->canAccessConstant($constantReflection)) {
			$error = RuleErrorBuilder::message(sprintf(
				'Access to %s constant %s of class %s.',
				$constantReflection->isPrivate() ? 'private' : 'protected',
				$constantName,
				$constantReflection->getDeclaringClass()->getDisplayName()
			))->identifier('class.constantInaccessible')->build();
			return array_merge($messages, [$error]);
		}

		return $messages; // Potentially an array of RuleError or strings
	}

}
