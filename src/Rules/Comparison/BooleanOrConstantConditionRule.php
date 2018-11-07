<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;

class BooleanOrConstantConditionRule implements \PHPStan\Rules\Rule
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
		return \PhpParser\Node\Expr\BinaryOp\BooleanOr::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp\BooleanOr $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		$messages = [];
		$leftType = $this->helper->getBooleanType($scope, $node->left);
		if ($leftType instanceof ConstantBooleanType) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Left side of || is always %s.',
				$leftType->getValue() ? 'true' : 'false'
			))->line($node->left->getLine())->build();
		}

		$rightType = $this->helper->getBooleanType(
			$scope->filterByFalseyValue($node->left),
			$node->right
		);
		if ($rightType instanceof ConstantBooleanType) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Right side of || is always %s.',
				$rightType->getValue() ? 'true' : 'false'
			))->line($node->right->getLine())->build();
		}

		if (count($messages) === 0) {
			$nodeType = $scope->getType($node);
			if ($nodeType instanceof ConstantBooleanType) {
				$messages[] = RuleErrorBuilder::message(sprintf(
					'Result of || is always %s.',
					$nodeType->getValue() ? 'true' : 'false'
				))->build();
			}
		}

		return $messages;
	}

}
