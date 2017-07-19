<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;

class DefinedVariableRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var bool
	 */
	private $cliArgumentsVariablesRegistered;

	public function __construct(bool $cliArgumentsVariablesRegistered)
	{
		$this->cliArgumentsVariablesRegistered = $cliArgumentsVariablesRegistered;
	}

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

		if ($this->cliArgumentsVariablesRegistered && in_array($node->name, [
			'argc',
			'argv',
		], true)) {
			$isInMain = !$scope->isInClass() && !$scope->isInAnonymousFunction() && $scope->getFunction() === null;
			if ($isInMain) {
				return [];
			}
		}

		if ($scope->isInExpressionAssign($node)) {
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
