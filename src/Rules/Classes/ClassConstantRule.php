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
		if (!($class instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$className = (string) $class;

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

		if (!$this->broker->hasClass($className)) {
			return [
				sprintf('Class %s not found.', $className),
			];
		}

		$constantName = $node->name;
		if ($constantName === 'class') {
			return [];
		}

		$classReflection = $this->broker->getClass($className);
		if (!$classReflection->hasConstant($constantName)) {
			return [
				sprintf('Access to undefined constant %s::%s.', $className, $constantName),
			];
		}

		return [];
	}

}
