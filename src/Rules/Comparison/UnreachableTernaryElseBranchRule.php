<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;

class UnreachableTernaryElseBranchRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\Ternary::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Ternary $node
	 * @param Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$conditionType = $scope->getType($node->cond)->toBoolean();
		if ($conditionType instanceof ConstantBooleanType && $conditionType->getValue()) {
			return [
				RuleErrorBuilder::message('Else branch is unreachable because ternary operator condition is always true.')->line($node->else->getLine())->build(),
			];
		}

		return [];
	}

}
