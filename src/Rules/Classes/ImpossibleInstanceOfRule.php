<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ObjectType;

class ImpossibleInstanceOfRule implements \PHPStan\Rules\Rule
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
		return Node\Expr\Instanceof_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Instanceof_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->class instanceof Node\Name) {
			$className = (string) $node->class;
			$type = new ObjectType($className);
		} else {
			$type = $scope->getType($node->class);
		}

		if ($type->getClass() === null) {
			return [];
		}

		$expressionType = $scope->getType($node->expr);
		if ($expressionType->getClass() === null) {
			return [];
		}

		if (!$this->broker->hasClass($expressionType->getClass())) {
			return [];
		}

		$expressionClassReflection = $this->broker->getClass($expressionType->getClass());
		if (!$this->broker->hasClass($type->getClass())) {
			return [];
		}

		if ($expressionClassReflection->isSubclassOf($type->getClass())) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to true.',
					$expressionType->describe(),
					$type->describe()
				),
			];
		}

		$classReflection = $this->broker->getClass($type->getClass());
		if ($classReflection->isInterface() || $expressionClassReflection->isInterface()) {
			return [];
		}

		if (!$expressionType->accepts($type)) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to false.',
					$expressionType->describe(),
					$type->describe()
				),
			];
		}

		return [];
	}

}
