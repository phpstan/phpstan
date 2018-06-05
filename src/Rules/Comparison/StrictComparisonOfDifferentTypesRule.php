<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\VerbosityLevel;

class StrictComparisonOfDifferentTypesRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $checkAlwaysTrueStrictComparison;

	public function __construct(bool $checkAlwaysTrueStrictComparison)
	{
		$this->checkAlwaysTrueStrictComparison = $checkAlwaysTrueStrictComparison;
	}

	public function getNodeType(): string
	{
		return Node\Expr\BinaryOp::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof Node\Expr\BinaryOp\Identical && !$node instanceof Node\Expr\BinaryOp\NotIdentical) {
			return [];
		}

		if (
			$this->isSpecifiedFunctionCall($scope, $node->left)
			|| $this->isSpecifiedFunctionCall($scope, $node->right)
		) {
			return [];
		}

		$nodeType = $scope->getType($node);
		if (!$nodeType instanceof ConstantBooleanType) {
			return [];
		}

		$leftType = $scope->getType($node->left);
		$rightType = $scope->getType($node->right);

		if (!$nodeType->getValue()) {
			return [
				sprintf(
					'Strict comparison using %s between %s and %s will always evaluate to false.',
					$node instanceof Node\Expr\BinaryOp\Identical ? '===' : '!==',
					$leftType->describe(VerbosityLevel::value()),
					$rightType->describe(VerbosityLevel::value())
				),
			];
		} elseif ($this->checkAlwaysTrueStrictComparison) {
			return [
				sprintf(
					'Strict comparison using %s between %s and %s will always evaluate to true.',
					$node instanceof Node\Expr\BinaryOp\Identical ? '===' : '!==',
					$leftType->describe(VerbosityLevel::value()),
					$rightType->describe(VerbosityLevel::value())
				),
			];
		}

		return [];
	}

	private function isSpecifiedFunctionCall(Scope $scope, Expr $node): bool
	{
		return (
			$node instanceof Expr\FuncCall
			|| $node instanceof Expr\MethodCall
			|| $node instanceof Expr\StaticCall
		) && $scope->isSpecified($node);
	}

}
