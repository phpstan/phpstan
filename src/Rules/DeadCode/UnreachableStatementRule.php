<?php declare(strict_types = 1);

namespace PHPStan\Rules\DeadCode;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\UnreachableStatementNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

class UnreachableStatementRule implements Rule
{

	public function getNodeType(): string
	{
		return UnreachableStatementNode::class;
	}

	/**
	 * @param UnreachableStatementNode $node
	 * @param Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->getOriginalStatement() instanceof Node\Stmt\Nop) {
			return [];
		}

		return [
			RuleErrorBuilder::message('Unreachable statement - code above always terminates.')->build(),
		];
	}

}
