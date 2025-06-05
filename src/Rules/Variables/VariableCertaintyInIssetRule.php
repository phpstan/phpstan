<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\NullType;

class VariableCertaintyInIssetRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\Isset_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Isset_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$messages = [];
		foreach ($node->vars as $var) {
			$isSubNode = false;
			while (
				$var instanceof Node\Expr\ArrayDimFetch
				|| $var instanceof Node\Expr\PropertyFetch
			) {
				$var = $var->var;
				$isSubNode = true;
			}

			while (
				$var instanceof Node\Expr\StaticPropertyFetch
				&& $var->class instanceof Node\Expr
			) {
				$var = $var->class;
				$isSubNode = true;
			}

			if (!$var instanceof Node\Expr\Variable || !is_string($var->name)) {
				continue;
			}

			if (DefinedVariableRule::isGlobalVariable($var->name)) {
				continue;
			}

			$certainty = $scope->hasVariableType($var->name);
			if ($certainty->no()) {
				$messages[] = RuleErrorBuilder::message(sprintf('Variable $%s in isset() is never defined.', $var->name))
					->identifier('variable.neverDefinedInIsset')
					->build();
			} elseif ($certainty->yes() && !$isSubNode) {
				$variableType = $scope->getVariableType($var->name);
				if (!$variableType->accepts(new NullType())) {
					$messages[] = RuleErrorBuilder::message(sprintf('Variable $%s in isset() always exists and is not nullable.', $var->name))
						->identifier('variable.alwaysExistsInIsset')
						->build();
				}
			}
		}

		return $messages;
	}

}
