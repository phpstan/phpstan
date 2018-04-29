<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

class InvalidIncDecOperationRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr::class;
	}

	/**
	 * @param \PhpParser\Node\Expr $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array
	{
		if (
			!$node instanceof \PhpParser\Node\Expr\PreInc
			&& !$node instanceof \PhpParser\Node\Expr\PostInc
			&& !$node instanceof \PhpParser\Node\Expr\PreDec
			&& !$node instanceof \PhpParser\Node\Expr\PostDec
		) {
			return [];
		}

		if (
			!$node->var instanceof \PhpParser\Node\Expr\Variable
			&& !$node->var instanceof \PhpParser\Node\Expr\ArrayDimFetch
			&& !$node->var instanceof \PhpParser\Node\Expr\PropertyFetch
			&& !$node->var instanceof \PhpParser\Node\Expr\StaticPropertyFetch
		) {
			return [
				sprintf(
					'Cannot use %s on a non-variable.',
					($node instanceof \PhpParser\Node\Expr\PreInc || $node instanceof \PhpParser\Node\Expr\PostInc) ? '++' : '--'
				),
			];
		}

		return [];
	}

}
