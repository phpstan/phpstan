<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Object_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\FloatType;
use PHPStan\Type\UnionType;
use PHPStan\TypeX\Is;

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
		if (Is::type($castType, FloatType::class) && $node->expr instanceof Node\Expr\BinaryOp\Div) {
			return [];
		}

		if ($castType->accepts($expressionType)) {
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
