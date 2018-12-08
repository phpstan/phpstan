<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;

class ConstantConditionRuleHelper
{

	/** @var ImpossibleCheckTypeHelper */
	private $impossibleCheckTypeHelper;

	public function __construct(ImpossibleCheckTypeHelper $impossibleCheckTypeHelper)
	{
		$this->impossibleCheckTypeHelper = $impossibleCheckTypeHelper;
	}

	public function getBooleanType(Scope $scope, Expr $expr): BooleanType
	{
		if (
			$expr instanceof Expr\Instanceof_
			|| $expr instanceof Expr\BinaryOp\Identical
			|| $expr instanceof Expr\BinaryOp\NotIdentical
			|| $expr instanceof Expr\BooleanNot
			|| $expr instanceof Expr\BinaryOp\BooleanOr
			|| $expr instanceof Expr\BinaryOp\BooleanAnd
			|| $expr instanceof Expr\Ternary
			|| $expr instanceof Expr\Isset_
		) {
			// already checked by different rules
			return new BooleanType();
		}

		if (
			$expr instanceof FuncCall
			|| $expr instanceof MethodCall
			|| $expr instanceof Expr\StaticCall
		) {
			$isAlways = $this->impossibleCheckTypeHelper->findSpecifiedType($scope, $expr);
			if ($isAlways !== null) {
				return new BooleanType();
			}
		}

		return $scope->getType($expr)->toBoolean();
	}

}
