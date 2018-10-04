<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\UnusedFunctionParametersCheck;

class UnusedClosureUsesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\UnusedFunctionParametersCheck */
	private $check;

	public function __construct(UnusedFunctionParametersCheck $check)
	{
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return Node\Expr\Closure::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Closure $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (count($node->uses) === 0) {
			return [];
		}

		return $this->check->getUnusedParameters(
			$scope,
			array_map(static function (Node\Expr\ClosureUse $use): string {
				if (!is_string($use->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				return $use->var->name;
			}, $node->uses),
			$node->stmts,
			'Anonymous function has an unused use $%s.'
		);
	}

}
