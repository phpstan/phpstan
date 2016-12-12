<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\AddContextTrait;

class ClassConstantRule implements \PHPStan\Rules\Rule
{
    use AddContextTrait;

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

        $errors = [];
		$classReflection = $this->broker->getClass($className);

        if ($classReflection->isTrait()) {
            foreach ($this->broker->getTraitUsers($className) as $userName => $userClass) {
                $errors = array_merge($this->processContextNode($node, $scope, $constantName, $className, $userClass, $userName), $errors);
            }
        } else {
            $errors = $this->processContextNode($node, $scope, $constantName, $className, $classReflection);
        }

        return $errors;
    }

    /**
     * Process a specific context of the node.
     *
     * Neessary for proper isolation of parsing in context (for trait handling).
     *
     * @param Node            $node            The active node.
     * @param Scope           $scope           The active scope.
     * @param string          $constantName    The constant name.
     * @param string          $className       The class name.
     * @param ClassReflection $classReflection The special refletion object.
     * @param string|null     $context         The context we're working under (for traits).  Optional.
     *
     * @return array The array of errors
     */
    public function processContextNode(
        Node $node,
        Scope $scope,
        string $constantName,
        string $className,
        ClassReflection $classReflection,
        string $context = null
    ): array {
		if (!$classReflection->hasConstant($constantName)) {
            return $this->addContext(
                [
                    sprintf('Access to undefined constant %s::%s.', $className, $constantName),
                ],
                $context
            );
		}

		$constantReflection = $classReflection->getConstant($constantName);
		if (!$scope->canAccessConstant($constantReflection)) {
            return $this->addContext(
                [
                    sprintf('Cannot access constant %s::%s from current scope.', $constantReflection->getDeclaringClass()->getName(), $constantName),
                ],
                $context
            );
		}

		return [];
	}
}
