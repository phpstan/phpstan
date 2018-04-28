<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;

class DefinedVariableRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $cliArgumentsVariablesRegistered;

	/** @var bool */
	private $checkMaybeUndefinedVariables;

	public function __construct(
		bool $cliArgumentsVariablesRegistered,
		bool $checkMaybeUndefinedVariables
	)
	{
		$this->cliArgumentsVariablesRegistered = $cliArgumentsVariablesRegistered;
		$this->checkMaybeUndefinedVariables = $checkMaybeUndefinedVariables;
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

		if ($scope->hasVariableType($node->name)->no()) {
			return [
				sprintf('Undefined variable: $%s', $node->name),
			];
		} elseif (
			$this->checkMaybeUndefinedVariables
			&& !$scope->hasVariableType($node->name)->yes()
		) {
			return [
				sprintf('Variable $%s might not be defined.', $node->name),
			];
		}

		return [];
	}

}
