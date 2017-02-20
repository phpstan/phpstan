<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\UnionType;

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

		if ($leftType instanceof UnionType || $rightType instanceof UnionType) {
			if ($leftType instanceof UnionType) {
				$unionType = $leftType;
				$otherType = $rightType;
			} else {
				$unionType = $rightType;
				$otherType = $leftType;
			}

			$isSameType = $unionType->accepts($otherType);
		} elseif ($leftType instanceof BooleanType && $rightType instanceof BooleanType) {
			$isSameType = $leftType->accepts($rightType) || $rightType->accepts($leftType);
		} elseif ($leftType instanceof StaticResolvableType || $rightType instanceof StaticResolvableType) {
			$isSameType = $leftType->accepts($rightType) || $rightType->accepts($leftType);
		} else {
			$isSameType = get_class($leftType) === get_class($rightType);
		}

		if (!$isSameType) {
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
