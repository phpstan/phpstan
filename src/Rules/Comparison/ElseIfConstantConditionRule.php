<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Type\Constant\ConstantBooleanType;

class ElseIfConstantConditionRule implements \PHPStan\Rules\Rule
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
		return \PhpParser\Node\Stmt\ElseIf_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ElseIf_ $node
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
					'Elseif condition is always %s.',
					$exprType->getValue() ? 'true' : 'false'
				),
			];
		}

		return [];
	}

}
