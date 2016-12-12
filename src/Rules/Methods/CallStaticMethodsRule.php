<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\AddContextTrait;
use PHPStan\Rules\FunctionCallParametersCheck;

class CallStaticMethodsRule implements \PHPStan\Rules\Rule
{
    use AddContextTrait;

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
		return StaticCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\StaticCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		$name = $node->name;
		$currentClass = $scope->getClass();
		if ($currentClass === null) {
			return [];
		}

		$currentClassReflection = $this->broker->getClass($currentClass);
		if (!($node->class instanceof Name)) {
			return [];
		}

		$class = (string) $node->class;
		if ($class === 'self' || $class === 'static') {
			$class = $currentClass;
		}

		if ($class === 'parent') {
			if ($currentClassReflection->getParentClass() === false) {
				return [
					sprintf(
						'%s::%s() calls to parent::%s() but %s does not extend any class.',
						$currentClass,
						$scope->getFunctionName(),
						$name,
						$currentClass
					),
				];
			}

			$currentMethodReflection = $currentClassReflection->getMethod(
				$scope->getFunctionName()
			);
			if (!$currentMethodReflection->isStatic()) {
				if ($name === '__construct' && $currentClassReflection->getParentClass()->hasMethod('__construct')) {
					return $this->check->check(
						$currentClassReflection->getParentClass()->getMethod('__construct'),
						$node,
						[
							'Parent constructor invoked with %d parameter, %d required.',
							'Parent constructor invoked with %d parameters, %d required.',
							'Parent constructor invoked with %d parameter, at least %d required.',
							'Parent constructor invoked with %d parameters, at least %d required.',
							'Parent constructor invoked with %d parameter, %d-%d required.',
							'Parent constructor invoked with %d parameters, %d-%d required.',
						]
					);
				}

				return [];
			}

			$class = $currentClassReflection->getParentClass()->getName();
		}

        $errors = [];
		$classReflection = $this->broker->getClass($class);

        if ($classReflection->isTrait()) {
            /**
             * Process trait check per using class.
             *
             * This is necessary for contextual processing of the trait, otherwise it
             * is very hard to identify in what context the trait is failing.
             */
            foreach ($this->broker->getTraitUsers($class) as $userName => $userClass) {
                $errors = array_merge($this->processContextNode($node, $scope, $name, $class, $userClass, $userName), $errors);
            }
        } else {
            $errors = $this->processContextNode($node, $scope, $name, $class, $classReflection);
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
     * @param string          $name            The method name.
     * @param string          $class           The class name.
     * @param ClassReflection $classReflection The special refletion object.
     * @param string|null     $context         The context we're working under (for traits).  Optional.
     *
     * @return array The array of errors
     */
    public function processContextNode(
        Node $node,
        Scope $scope,
        string $name,
        string $class,
        ClassReflection $classReflection,
        string $context = null
    ): array {
        if (!$classReflection->hasMethod($name)) {
            return $this->addContext(
                [
                    sprintf(
                        'Call to an undefined static method %s::%s().',
                        $class,
                        $name
                    ),
                ],
                $context
            );
        }

        $method = $classReflection->getMethod($name);
        if (!$method->isStatic()) {
            return $this->addContext(
                [
                    sprintf(
                        'Static call to instance method %s::%s().',
                        $class,
                        $method->getName()
                    ),
                ],
                $context);
        }

        if (!$scope->canCallMethod($method)) {
            return $this->addContext(
                [
                    sprintf(
                        'Call to %s static method %s() of class %s.',
                        $method->isPrivate() ? 'private' : 'protected',
                        $method->getName(),
                        $method->getDeclaringClass()->getName()
                    ),
                ],
                $context
            );
        }

        $methodName = $class . '::' . $method->getName() . '()';

        $errors = $this->check->check(
            $method,
            $node,
            [
                'Static method ' . $methodName . ' invoked with %d parameter, %d required.',
                'Static method ' . $methodName . ' invoked with %d parameters, %d required.',
                'Static method ' . $methodName . ' invoked with %d parameter, at least %d required.',
                'Static method ' . $methodName . ' invoked with %d parameters, at least %d required.',
                'Static method ' . $methodName . ' invoked with %d parameter, %d-%d required.',
                'Static method ' . $methodName . ' invoked with %d parameters, %d-%d required.',
            ]
        );

        if ($method->getName() !== $name) {
            $errors[] = sprintf('Call to static method %s with incorrect case: %s', $methodName, $name);
        }

        return $this->addContext($errors, $context);
    }
}
