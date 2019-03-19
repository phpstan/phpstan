<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PHPStan\Rules\Rule;

class EvaluationOrderRule implements Rule
{

	public function getNodeType(): string
	{
		return Node::class;
	}

	/**
	 * @param Node $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (
			$node instanceof Node\Expr\FuncCall
			&& $node->name instanceof Node\Name
		) {
			return [$node->name->toString()];
		}

		if ($node instanceof Node\Scalar\String_) {
			return [$node->value];
		}

		return [];
	}

}
