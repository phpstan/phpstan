<?php declare(strict_types = 1);

namespace PHPStan\Rules\Constants;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

class ConstantRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\ConstFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ConstFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->hasConstant($node->name)) {
			return [
				sprintf(
					'Constant %s not found.',
					(string) $node->name
				),
			];
		}

		return [];
	}

}
