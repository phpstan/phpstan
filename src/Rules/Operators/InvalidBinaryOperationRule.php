<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;

class InvalidBinaryOperationRule implements \PHPStan\Rules\Rule
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
			!$node instanceof Node\Expr\BinaryOp
			&& !$node instanceof Node\Expr\AssignOp
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
					'Binary operation "%s" between %s and %s results in an error.',
					substr(substr($this->printer->prettyPrintExpr($newNode), 2), 0, -2),
					$scope->getType($left)->describe(),
					$scope->getType($right)->describe()
				),
			];
		}

		return [];
	}

}
