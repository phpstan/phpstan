<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Expr\BinaryOp;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\VerbosityLevel;

class NumberComparisonOperatorsConstantConditionRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return BinaryOp::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		if (
			!$node instanceof BinaryOp\Greater
			&& !$node instanceof BinaryOp\GreaterOrEqual
			&& !$node instanceof BinaryOp\Smaller
			&& !$node instanceof BinaryOp\SmallerOrEqual
		) {
			return [];
		}

		$exprType = $scope->getType($node);
		if ($exprType instanceof ConstantBooleanType) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Comparison operation "%s" between %s and %s is always %s.',
					$node->getOperatorSigil(),
					$scope->getType($node->left)->describe(VerbosityLevel::value()),
					$scope->getType($node->right)->describe(VerbosityLevel::value()),
					$exprType->getValue() ? 'true' : 'false'
				))->build(),
			];
		}

		return [];
	}

}
