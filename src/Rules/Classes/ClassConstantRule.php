<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class ClassConstantRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
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
		$class = $node->class;
		if ($class instanceof \PhpParser\Node\Name) {
			$className = (string) $class;
		} elseif ($class instanceof Node\Expr) {
			$classType = $scope->getType($class);
			if ($classType->getClass() !== null) {
				$className = $classType->getClass();
			} else {
				return [];
			}
		} else {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$constantName = $node->name;
		if ($className === 'self' || $className === 'static') {
			if (!$scope->isInClass()) {
				return [
					sprintf('Using %s outside of class scope.', $className),
				];
			}

			$classReflection = $scope->getClassReflection();
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
			$classReflection = $currentClassReflection->getParentClass();
		} else {
			if (!$this->broker->hasClass($className)) {
				return [
					sprintf('Class %s not found.', $className),
				];
			}

			$classReflection = $this->broker->getClass($className);
		}

		if (!is_string($constantName) || $constantName === 'class') {
			return [];
		}

		if (!$classReflection->hasConstant($constantName)) {
			return [
				sprintf(
					'Access to undefined constant %s::%s.',
					$classReflection->getDisplayName(),
					$constantName
				),
			];
		}

		$constantReflection = $classReflection->getConstant($constantName);
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
