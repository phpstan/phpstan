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
		} else {
			$classType = $scope->getType($class);
			if ($classType->getClass() !== null) {
				$className = $classType->getClass();
			} else {
				return [];
			}
		}

		if ($className === 'self' || $className === 'static') {
			if ($scope->getClass() === null && !$scope->isInAnonymousClass()) {
				return [
					sprintf('Using %s outside of class scope.', $className),
				];
			}

			if ($className === 'static') {
				return [];
			}

			if ($className === 'self') {
				$className = $scope->getClass();
			}
		}

		$constantName = $node->name;
		if ($scope->getClass() !== null && $className === 'parent') {
			$currentClassReflection = $this->broker->getClass($scope->getClass());
			if ($currentClassReflection->getParentClass() === false) {
				return [
					sprintf(
						'Access to parent::%s but %s does not extend any class.',
						$constantName,
						$scope->getClass()
					),
				];
			}

			$className = $currentClassReflection->getParentClass()->getName();
		}

		if (!$this->broker->hasClass($className)) {
			return [
				sprintf('Class %s not found.', $className),
			];
		}

		if ($constantName === 'class') {
			return [];
		}

		$classReflection = $this->broker->getClass($className);
		if (!$classReflection->hasConstant($constantName)) {
            // If we're reflecting a trait, perform analysis on each context of the trait.
            if ($classReflection->isTrait()) {
                $errors = [];

                // List of all available classes that use this trait...
                foreach ($this->broker->getTraitUsers($className) as $userName => $userClass) {
                    if (!$userClass->hasConstant($constantName)) {
                        $errors[] = sprintf(
                            'Access to undefined constant %s::%s in %s context.',
                            $className,
                            $constantName,
                            $userName
                        );
                    }
                }

                // If we didn't find errors, we can just keep on going...
                if (!empty($errors)) {
                    return $errors;
                }
            } else {
                return [
                    sprintf('Access to undefined constant %s::%s.', $className, $constantName),
                ];
            }
		}

		$constantReflection = $classReflection->getConstant($constantName);
		if (!$scope->canAccessConstant($constantReflection)) {
			return [
				sprintf('Cannot access constant %s::%s from current scope.', $constantReflection->getDeclaringClass()->getName(), $constantName),
			];
		}

		return [];
	}

}
