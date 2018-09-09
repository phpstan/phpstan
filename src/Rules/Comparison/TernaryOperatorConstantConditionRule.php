<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ConstantType;

class TernaryOperatorConstantConditionRule implements \PHPStan\Rules\Rule
{

	/** @var ConstantConditionRuleHelper */
	private $helper;

	public function __construct(
		ConstantConditionRuleHelper $helper
	)
	{
		$this->helper = $helper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\Ternary::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Ternary $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		$exprType = $this->helper->getBooleanType($scope, $node->cond);
		if ($exprType instanceof ConstantBooleanType) {
			return [
				sprintf(
					'Ternary operator condition is always %s.',
					$exprType->getValue() ? 'true' : 'false'
				),
			];
		}

		$ifType = $node->if === null ? $scope->getType($node->cond) : $scope->getType($node->if);
		$elseType = $scope->getType($node->else);

		if ($ifType instanceof ConstantType && $elseType instanceof ConstantType) {
			if ($ifType->equals($elseType)) {
				return ['If end else parts of ternary operator are equal'];
			}
			if ($ifType instanceof ConstantBooleanType && $elseType instanceof ConstantBooleanType) {
				return ['Ternary operator is not needed. Use just condition casted to bool'];
			}
		}
		return [];
	}

}
