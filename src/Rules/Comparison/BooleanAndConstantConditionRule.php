<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Type\Constant\ConstantBooleanType;

class BooleanAndConstantConditionRule implements \PHPStan\Rules\Rule
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
		return \PhpParser\Node\Expr\BinaryOp\BooleanAnd::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp\BooleanAnd $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		$messages = [];
		$leftType = $this->helper->getBooleanType($scope, $node->left);
		if ($leftType instanceof ConstantBooleanType) {
			$messages[] = sprintf(
				'Left side of && is always %s.',
				$leftType->getValue() ? 'true' : 'false'
			);
		}

		$rightType = $this->helper->getBooleanType(
			$scope->filterByTruthyValue($node->left),
			$node->right
		);
		if ($rightType instanceof ConstantBooleanType) {
			$messages[] = sprintf(
				'Right side of && is always %s.',
				$rightType->getValue() ? 'true' : 'false'
			);
		}

		if (count($messages) === 0) {
			$nodeType = $scope->getType($node);
			if ($nodeType instanceof ConstantBooleanType) {
				$messages[] = sprintf(
					'Result of && is always %s.',
					$nodeType->getValue() ? 'true' : 'false'
				);
			}
		}

		return $messages;
	}

}
