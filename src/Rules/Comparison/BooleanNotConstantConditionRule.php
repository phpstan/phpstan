<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Type\Constant\ConstantBooleanType;

class BooleanNotConstantConditionRule implements \PHPStan\Rules\Rule
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
		return \PhpParser\Node\Expr\BooleanNot::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\BooleanNot $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		\PHPStan\Analyser\Scope $scope
	): array
	{
		$exprType = $this->helper->getBooleanType($scope, $node->expr);
		if ($exprType instanceof ConstantBooleanType) {
			return [
				sprintf(
					'Negated boolean is always %s.',
					$exprType->getValue() ? 'false' : 'true'
				),
			];
		}

		return [];
	}

}
