<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;

class UnreachableIfBranchesRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Node\Stmt\If_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\If_ $node
	 * @param Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];
		$conditionType = $scope->getType($node->cond)->toBoolean();
		$nextBranchIsDead = $conditionType instanceof ConstantBooleanType && $conditionType->getValue();

		foreach ($node->elseifs as $elseif) {
			if ($nextBranchIsDead) {
				$errors[] = RuleErrorBuilder::message('Elseif branch is unreachable because previous condition is always true.')->line($elseif->getLine())->build();
				continue;
			}

			$elseIfConditionType = $scope->getType($elseif->cond)->toBoolean();
			$nextBranchIsDead = $elseIfConditionType instanceof ConstantBooleanType && $elseIfConditionType->getValue();
		}

		if ($node->else !== null && $nextBranchIsDead) {
			$errors[] = RuleErrorBuilder::message('Else branch is unreachable because previous condition is always true.')->line($node->else->getLine())->build();
		}

		return $errors;
	}

}
