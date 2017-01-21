<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;

class RequireParentConstructCallRule implements \PHPStan\Rules\Rule
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
		return ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->name !== '__construct') {
			return [];
		}

		$className = $scope->getClass();
		if ($className === null) {
			return []; // anonymous class
		}

		$classReflection = $this->broker->getClass($className);
		if ($classReflection->isInterface()) {
			return [];
		}

		if ($this->callsParentConstruct($node)) {
			if ($classReflection->getParentClass() === false) {
				return [
					sprintf(
						'%s::__construct() calls parent constructor but does not extend any class.',
						$className
					),
				];
			}

			if ($this->getParentConstructorClass($classReflection, $scope) === false) {
				return [
					sprintf(
						'%s::__construct() calls parent constructor but parent does not have one.',
						$className
					),
				];
			}
		} else {
			$parentClass = $this->getParentConstructorClass($classReflection, $scope);
			if ($parentClass !== false) {
				return [
					sprintf(
						'%s::__construct() does not call parent constructor from %s.',
						$className,
						$parentClass->getName()
					),
				];
			}
		}

		return [];
	}

	private function callsParentConstruct(Node $parserNode): bool
	{
		if (!isset($parserNode->stmts)) {
			return false;
		}

		foreach ($parserNode->stmts as $statement) {
			if ($statement instanceof \PhpParser\Node\Expr\StaticCall) {
				if (
					$statement->class instanceof Name
					&& ((string) $statement->class === 'parent')
					&& $statement->name === '__construct'
				) {
					return true;
				}
			} else {
				if ($this->callsParentConstruct($statement)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\ClassReflection|boolean
	 */
	private function getParentConstructorClass(ClassReflection $classReflection, Scope $scope)
	{
		while ($classReflection->getParentClass() !== false) {
			if (
				(
					$classReflection->getParentClass()->hasMethod('__construct')
					&& $classReflection->getParentClass()->getMethod('__construct', $scope)->getDeclaringClass()->getName() === $classReflection->getParentClass()->getName()
				) || (
					$classReflection->getParentClass()->hasMethod($classReflection->getParentClass()->getName())
					&& $classReflection->getParentClass()->getMethod($classReflection->getParentClass()->getName(), $scope)->getDeclaringClass()->getName() === $classReflection->getParentClass()->getName()
				)
			) {
				return $classReflection->getParentClass();
			}

			$classReflection = $classReflection->getParentClass();
		}

		return false;
	}

}
