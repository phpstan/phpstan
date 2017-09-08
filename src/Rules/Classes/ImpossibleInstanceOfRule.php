<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;

class ImpossibleInstanceOfRule implements \PHPStan\Rules\Rule
{

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

		$expressionType = $scope->getType($node->expr);
		$isSuperset = $type->isSupersetOf($expressionType);

		if ($isSuperset->yes()) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to true.',
					$expressionType->describe(),
					$type->describe()
				),
			];
		} elseif ($isSuperset->no()) {
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
