<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\ClosureUse;
use PHPStan\Analyser\Scope;

class DefinedVariableInAnonymousFunctionUseRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $checkMaybeUndefinedVariables;

	public function __construct(
		bool $checkMaybeUndefinedVariables
	)
	{
		$this->checkMaybeUndefinedVariables = $checkMaybeUndefinedVariables;
	}

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
		if ($node->byRef || !is_string($node->var->name)) {
			return [];
		}

		if ($scope->hasVariableType($node->var->name)->no()) {
			return [
				sprintf('Undefined variable: $%s', $node->var->name),
			];
		} elseif (
			$this->checkMaybeUndefinedVariables
			&& !$scope->hasVariableType($node->var->name)->yes()
		) {
			return [
				sprintf('Variable $%s might not be defined.', $node->var->name),
			];
		}

		return [];
	}

}
