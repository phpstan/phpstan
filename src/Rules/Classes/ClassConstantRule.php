<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
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

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
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
		if ($class instanceof \PhpParser\Node\Name) {
			$className = (string) $class;
			if ($className === 'self' || $className === 'static') {
				if (!$scope->isInClass()) {
					return [
						sprintf('Using %s outside of class scope.', $className),
					];
				}

				$className = $scope->getClassReflection()->getName();
			} elseif ($className === 'parent') {
				if (!$scope->isInClass()) {
					return [
						sprintf('Using %s outside of class scope.', $className),
					];
				}
				$currentClassReflection = $scope->getClassReflection();
				if ($currentClassReflection->getParentClass() === false) {
					return [
						sprintf(
							'Access to parent::%s but %s does not extend any class.',
							$constantName,
							$currentClassReflection->getDisplayName()
						),
					];
				}
				$className = $currentClassReflection->getParentClass()->getName();
			} elseif (!$this->broker->hasClass($className)) {
				if (strtolower($constantName) === 'class') {
					return [
						sprintf('Class %s not found.', $className),
					];
				}

				return [
					sprintf('Access to constant %s on an unknown class %s.', $constantName, $className),
				];
			}

			$classType = new ObjectType($className);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$class,
				sprintf('Access to constant %s on an unknown class %%s.', $constantName)
			);
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				return $classTypeResult->getUnknownClassErrors();
			}
		}

		if ($classType instanceof StringType) {
			return [];
		}

		if (!$classType->canAccessConstants()) {
			return [
				sprintf('Cannot access constant %s on %s.', $constantName, $classType->describe()),
			];
		}

		if (strtolower($constantName) === 'class') {
			return [];
		}

		if (!$classType->hasConstant($constantName)) {
			return [
				sprintf(
					'Access to undefined constant %s::%s.',
					$classType->describe(),
					$constantName
				),
			];
		}

		$constantReflection = $classType->getConstant($constantName);
		if (!$scope->canAccessConstant($constantReflection)) {
			return [
				sprintf(
					'Access to %s constant %s of class %s.',
					$constantReflection->isPrivate() ? 'private' : 'protected',
					$constantName,
					$constantReflection->getDeclaringClass()->getDisplayName()
				),
			];
		}

		return [];
	}

}
