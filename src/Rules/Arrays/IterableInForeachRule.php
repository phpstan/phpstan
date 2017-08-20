<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;

class IterableInForeachRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\Foreach_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Foreach_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		$iteratedExpressionType = $scope->getType($node->expr);
		if ($iteratedExpressionType instanceof UnionType) {
			return [];
		}

		if ($iteratedExpressionType->isIterable()->no()) {
			return [
				sprintf(
					'Argument of an invalid type %s supplied for foreach, only iterables are supported.',
					$iteratedExpressionType->describe()
				),
			];
		}

		return [];
	}

}
