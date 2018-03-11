<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Type\Constant\ConstantBooleanType;

class BooleanOrConstantConditionRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\BinaryOp\BooleanOr::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp\BooleanOr $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		$messages = [];
		$leftType = ConstantConditionRuleHelper::getBooleanType($scope, $node->left);
		if ($leftType instanceof ConstantBooleanType) {
			$messages[] = sprintf(
				'Left side of || is always %s.',
				$leftType->getValue() ? 'true' : 'false'
			);
		}

		$rightType = ConstantConditionRuleHelper::getBooleanType(
			$scope->filterByFalseyValue($node->left),
			$node->right
		);
		if ($rightType instanceof ConstantBooleanType) {
			$messages[] = sprintf(
				'Right side of || is always %s.',
				$rightType->getValue() ? 'true' : 'false'
			);
		}

		return $messages;
	}

}
