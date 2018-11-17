<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;

class IfConstantConditionRule implements \PHPStan\Rules\Rule
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
		return \PhpParser\Node\Stmt\If_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\If_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		$exprType = $this->helper->getBooleanType($scope, $node->cond);
		if ($exprType instanceof ConstantBooleanType) {
			return [
				RuleErrorBuilder::message(sprintf(
					'If condition is always %s.',
					$exprType->getValue() ? 'true' : 'false'
				))->line($node->cond->getLine())->build(),
			];
		}

		return [];
	}

}
