<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;

class DefinedVariableRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Variable::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Variable $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		if (in_array($node->name, [
			'GLOBALS',
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_COOKIE',
			'_SESSION',
			'_REQUEST',
			'_ENV',
		], true)) {
			return [];
		}

		if ($scope->isInVariableAssign($node->name)) {
			return [];
		}

		if (!$scope->hasVariableType($node->name)) {
			return [
				sprintf('Undefined variable: $%s', $node->name),
			];
		}

		return [];
	}

}
