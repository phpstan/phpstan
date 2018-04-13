<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\NullType;
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

		$leftType = $scope->getType($node->left);
		$rightType = $scope->getType($node->right);

		if (
			(
				$node->left instanceof Node\Expr\PropertyFetch
				|| $node->left instanceof Node\Expr\StaticPropertyFetch
			)
			&& $rightType instanceof NullType
		) {
			return [];
		}

		if (
			(
				$node->right instanceof Node\Expr\PropertyFetch
				|| $node->right instanceof Node\Expr\StaticPropertyFetch
			)
			&& $leftType instanceof NullType
		) {
			return [];
		}

		$nodeType = $scope->getType($node);
		if (
			$nodeType instanceof ConstantBooleanType
			&& (
				(
					$node instanceof Node\Expr\BinaryOp\Identical
					&& !$nodeType->getValue()
				) || (
					$node instanceof Node\Expr\BinaryOp\NotIdentical
					&& $nodeType->getValue()
				)
			)
		) {
			return [
				sprintf(
					'Strict comparison using %s between %s and %s will always evaluate to %s.',
					$node instanceof Node\Expr\BinaryOp\Identical ? '===' : '!==',
					$leftType->describe(VerbosityLevel::value()),
					$rightType->describe(VerbosityLevel::value()),
					$node instanceof Node\Expr\BinaryOp\Identical ? 'false' : 'true'
				),
			];
		} elseif (
			$this->checkAlwaysTrueStrictComparison
			&& $nodeType instanceof ConstantBooleanType
			&& (
				(
					$node instanceof Node\Expr\BinaryOp\Identical
					&& $nodeType->getValue()
				) || (
					$node instanceof Node\Expr\BinaryOp\NotIdentical
					&& !$nodeType->getValue()
				)
			)
		) {
			return [
				sprintf(
					'Strict comparison using %s between %s and %s will always evaluate to %s.',
					$node instanceof Node\Expr\BinaryOp\Identical ? '===' : '!==',
					$leftType->describe(VerbosityLevel::value()),
					$rightType->describe(VerbosityLevel::value()),
					$node instanceof Node\Expr\BinaryOp\Identical ? 'true' : 'false'
				),
			];
		}

		return [];
	}

}
