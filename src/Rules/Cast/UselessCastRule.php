<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Object_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\FloatType;
use PHPStan\Type\UnionType;

class UselessCastRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Cast::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Cast $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node instanceof Object_) {
			return [];
		}

		$expressionType = $scope->getType($node->expr);
		if ($expressionType instanceof UnionType) {
			return [];
		}

		$castType = $scope->getType($node);
		if ($castType instanceof FloatType && $node->expr instanceof Node\Expr\BinaryOp\Div) {
			return [];
		}

		if (get_class($expressionType) === get_class($castType)) {
			return [
				sprintf(
					'Casting to %s something that\'s already %s.',
					$castType->describe(),
					$expressionType->describe()
				),
			];
		}

		return [];
	}

}
