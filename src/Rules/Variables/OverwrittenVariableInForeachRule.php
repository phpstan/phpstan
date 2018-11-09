<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;

class OverwrittenVariableInForeachRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Foreach_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Foreach_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];

		foreach ([$node->valueVar, $node->keyVar] as $variable) {
			if (!$variable instanceof Variable || !is_string($variable->name)) {
				continue;
			}

			$variableName = $variable->name;

			if (!$scope->hasVariableType($variableName)->yes()) {
				continue;
			}

			$errors[] = sprintf(
				'foreach will overwrite variable $%s.',
				$variableName
			);
		}

		return $errors;
	}

}
