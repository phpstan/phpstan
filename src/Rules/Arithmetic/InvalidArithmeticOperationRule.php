<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arithmetic;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;

class InvalidArithmeticOperationRule implements \PHPStan\Rules\Rule
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	public function __construct(\PhpParser\PrettyPrinter\Standard $printer)
	{
		$this->printer = $printer;
	}

	public function getNodeType(): string
	{
		return Node::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (
			!$node instanceof Node\Expr\BinaryOp\Plus
			&& !$node instanceof Node\Expr\BinaryOp\Minus
			&& !$node instanceof Node\Expr\BinaryOp\Mul
			&& !$node instanceof Node\Expr\BinaryOp\Pow
			&& !$node instanceof Node\Expr\BinaryOp\Div
			&& !$node instanceof Node\Expr\BinaryOp\Mod
			&& !$node instanceof Node\Expr\AssignOp\Plus
			&& !$node instanceof Node\Expr\AssignOp\Minus
			&& !$node instanceof Node\Expr\AssignOp\Mul
			&& !$node instanceof Node\Expr\AssignOp\Pow
			&& !$node instanceof Node\Expr\AssignOp\Div
			&& !$node instanceof Node\Expr\AssignOp\Mod
		) {
			return [];
		}

		$literalOne = new Node\Scalar\LNumber(1);
		if ($node instanceof Node\Expr\AssignOp) {
			$newNode = clone $node;
			$left = $node->var;
			$right = $node->expr;
			$newNode->var = $literalOne;
			$newNode->expr = $literalOne;
		} else {
			$newNode = clone $node;
			$left = $node->left;
			$right = $node->right;
			$newNode->left = $literalOne;
			$newNode->right = $literalOne;
		}

		if ($scope->getType($node) instanceof ErrorType) {
			return [
				sprintf(
					'Arithmetic operation "%s" between %s and %s results in an error.',
					substr(substr($this->printer->prettyPrintExpr($newNode), 2), 0, -2),
					$scope->getType($left)->describe(),
					$scope->getType($right)->describe()
				),
			];
		}

		return [];
	}

}
