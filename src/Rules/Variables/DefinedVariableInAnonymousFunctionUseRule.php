<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\ClosureUse;
use PHPStan\Analyser\Scope;

class DefinedVariableInAnonymousFunctionUseRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return ClosureUse::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ClosureUse $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->byRef) {
			return [];
		}

		if (!$scope->hasVariableType($node->var)) {
			return [
				sprintf('Undefined variable: $%s', $node->var),
			];
		}

		return [];
	}

}
