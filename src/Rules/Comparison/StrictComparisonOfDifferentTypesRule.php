<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;

class StrictComparisonOfDifferentTypesRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\BinaryOp::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof Node\Expr\BinaryOp\Identical && !$node instanceof Node\Expr\BinaryOp\NotIdentical) {
			return [];
		}

		$leftType = $scope->getType($node->left);
		$rightType = $scope->getType($node->right);

		if (
			$leftType instanceof MixedType
			|| $rightType instanceof MixedType
			|| $leftType instanceof NullType
			|| $rightType instanceof NullType
		) {
			return [];
		}

		if (get_class($leftType) !== get_class($rightType)) {
			return [
				sprintf(
					'Strict comparison using %s between %s and %s will always evaluate to false.',
					$node instanceof Node\Expr\BinaryOp\Identical ? '===' : '!==',
					$leftType->describe(),
					$rightType->describe()
				),
			];
		}

		return [];
	}

}
