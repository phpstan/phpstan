<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Type\Constant\ConstantBooleanType;

class IfConstantConditionRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\If_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\If_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		$exprType = ConstantConditionRuleHelper::getBooleanType($scope, $node->cond);
		if ($exprType instanceof ConstantBooleanType) {
			return [
				sprintf(
					'If condition is always %s.',
					$exprType->getValue() ? 'true' : 'false'
				),
			];
		}

		return [];
	}

}
