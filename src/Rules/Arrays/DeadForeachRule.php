<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class DeadForeachRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Stmt\Foreach_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Foreach_ $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$iterableType = $scope->getType($node->expr);
		if ($iterableType->isIterable()->no()) {
			return [];
		}

		if (!$iterableType->isIterableAtLeastOnce()->no()) {
			return [];
		}

		return [
			'Empty array passed to foreach.',
		];
	}

}
