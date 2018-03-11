<?php declare(strict_types = 1);

namespace PHPStan\Rules\Conditions;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\TrinaryLogic;

class ConstantConditionRule implements \PHPStan\Rules\Rule
{

	/** @var TypeSpecifier */
	private $typeSpecifier;

	public function __construct(TypeSpecifier $typeSpecifier)
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\If_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\If_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$all = [];

		$all[] = $this->checkCondition($node->cond, $scope);
		$scope = $scope->filterByFalseyValue($node->cond);

		foreach ($node->elseifs as $elseif) {
			$all[] = $this->checkCondition($elseif->cond, $scope);
			$scope = $scope->filterByFalseyValue($node->cond);
		}

		return array_merge(...$all);
	}


	protected function checkCondition(Expr $condition, Scope $scope): array
	{
		$sureTypes = $this->typeSpecifier->specifyTypesInCondition($scope, $condition)->getSureTypes();

		$subResults = [];
		foreach ($sureTypes as list($exprNode, $typeAfterCondition)) {
			$typeBeforeCondition = $scope->getType($exprNode);
			$subResults[] = $typeAfterCondition->isSupersetOf($typeBeforeCondition);
		}

		$finalResult = TrinaryLogic::extremeIdentity(...$subResults);
		if ($finalResult->no()) {
			return ['Condition will always evaluate to false.'];

		} elseif ($finalResult->yes()) {
			return ['Condition will always evaluate to true.'];
		}

		return [];
	}
}
