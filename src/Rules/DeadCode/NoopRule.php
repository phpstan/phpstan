<?php declare(strict_types = 1);

namespace PHPStan\Rules\DeadCode;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class NoopRule implements Rule
{

	/** @var Standard */
	private $printer;

	public function __construct(Standard $printer)
	{
		$this->printer = $printer;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Expression::class;
	}

	/**
	 * @param Node\Stmt\Expression $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$expr = $node->expr;
		if (
			!$expr instanceof Node\Expr\Variable
			&& !$expr instanceof Node\Expr\PropertyFetch
			&& !$expr instanceof Node\Expr\StaticPropertyFetch
			&& !$expr instanceof Node\Expr\ArrayDimFetch
			&& !$expr instanceof Node\Scalar
		) {
			return [];
		}

		return [
			sprintf(
				'Expression "%s" on a separate line does not do anything.',
				$this->printer->prettyPrintExpr($expr)
			),
		];
	}

}
