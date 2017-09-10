<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;

class RequireParentConstructCallRule implements \PHPStan\Rules\Rule
{

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
		if (strpos($scope->getAnalysedContextFile(), '(in context of ') !== false) {
			return []; // skip traits
		}

		if ($node->name !== '__construct') {
			return [];
		}

		$classReflection = $scope->getClassReflection()->getNativeReflection();
		if ($classReflection->isInterface() || $classReflection->isAnonymous()) {
			return [];
		}

		if ($this->callsParentConstruct($node)) {
			if ($classReflection->getParentClass() === false) {
				return [
					sprintf(
						'%s::__construct() calls parent constructor but does not extend any class.',
						$classReflection->getName()
					),
				];
			}

			if ($this->getParentConstructorClass($classReflection) === false) {
				return [
					sprintf(
						'%s::__construct() calls parent constructor but parent does not have one.',
						$classReflection->getName()
					),
				];
			}
		} else {
			$parentClass = $this->getParentConstructorClass($classReflection);
			if ($parentClass !== false) {
				return [
					sprintf(
						'%s::__construct() does not call parent constructor from %s.',
						$classReflection->getName(),
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
			$statement = $this->ignoreErrorSuppression($statement);
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
	 * @param \ReflectionClass $classReflection
	 * @return \ReflectionClass|false
	 */
	private function getParentConstructorClass(\ReflectionClass $classReflection)
	{
		while ($classReflection->getParentClass() !== false) {
			$constructor = $classReflection->getParentClass()->hasMethod('__construct') ? $classReflection->getParentClass()->getMethod('__construct') : null;
			$constructorWithClassName = $classReflection->getParentClass()->hasMethod($classReflection->getParentClass()->getName()) ? $classReflection->getParentClass()->getMethod($classReflection->getParentClass()->getName()) : null;
			if (
				(
					$constructor !== null
					&& $constructor->getDeclaringClass()->getName() === $classReflection->getParentClass()->getName()
					&& !$constructor->isAbstract()
				) || (
					$constructorWithClassName !== null
					&& $constructorWithClassName->getDeclaringClass()->getName() === $classReflection->getParentClass()->getName()
					&& !$constructorWithClassName->isAbstract()
				)
			) {
				return $classReflection->getParentClass();
			}

			$classReflection = $classReflection->getParentClass();
		}

		return false;
	}

	private function ignoreErrorSuppression(Node $statement): Node
	{
		if ($statement instanceof Node\Expr\ErrorSuppress) {

			return $statement->expr;
		}

		return $statement;
	}

}
