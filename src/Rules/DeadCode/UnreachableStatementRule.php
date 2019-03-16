<?php declare(strict_types = 1);

namespace PHPStan\Rules\DeadCode;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\UnreachableStatementNode;
use PHPStan\Rules\Rule;

class UnreachableStatementRule implements Rule
{

	public function getNodeType(): string
	{
		return UnreachableStatementNode::class;
	}

	/**
	 * @param UnreachableStatementNode $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->getOriginalStatement() instanceof Node\Stmt\Nop) {
			return [];
		}

		return [
			'Unreachable statement - code above always terminates.',
		];
	}

}
