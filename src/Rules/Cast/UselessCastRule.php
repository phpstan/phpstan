<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Object_;
use PHPStan\Analyser\Scope;

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
		$castType = $scope->getType($node);
		if ($castType->isSuperTypeOf($expressionType)->yes()) {
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
