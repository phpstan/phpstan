<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Rules\RuleErrorBuilder;

class UnusedFunctionParametersCheck
{

	/**
	 * @param string[] $parameterNames
	 * @param \PhpParser\Node[] $statements
	 * @param string $unusedParameterMessage
	 * @return array<mixed>|\PHPStan\Rules\RuleError[]
	 */
	public function getUnusedParameters(
		array $parameterNames,
		array $statements,
		string $unusedParameterMessage
	): array
	{
		$unusedParameters = array_fill_keys($parameterNames, true);
		foreach ($this->getUsedVariables($statements) as $variableName) {
			if (isset($unusedParameters[$variableName])) {
				unset($unusedParameters[$variableName]);
			}
		}
		$errors = [];
		foreach ($unusedParameters as $name => $bool) {
			$errors[] = RuleErrorBuilder::message(sprintf($unusedParameterMessage, $name))
				->identifier('parameter.unused')
				->build();
		}

		return $errors;
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node $node
	 * @return string[]
	 */
	private function getUsedVariables($node): array
	{
		$variableNames = [];
		if ($node instanceof Node) {
			if ($node instanceof Node\Expr\Variable && is_string($node->name) && $node->name !== 'this') {
				return [$node->name];
			}
			if ($node instanceof Node\Expr\ClosureUse) {
				return [$node->var];
			}
			if (
				$node instanceof Node\Expr\FuncCall
				&& $node->name instanceof Node\Name
				&& (string) $node->name === 'compact'
			) {
				foreach ($node->args as $arg) {
					if ($arg->value instanceof Node\Scalar\String_) {
						$variableNames[] = $arg->value->value;
					}
				}
			}
			foreach ($node->getSubNodeNames() as $subNodeName) {
				if ($node instanceof Node\Expr\Closure && $subNodeName !== 'uses') {
					continue;
				}
				$subNode = $node->{$subNodeName};
				$variableNames = array_merge($variableNames, $this->getUsedVariables($subNode));
			}
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$variableNames = array_merge($variableNames, $this->getUsedVariables($subNode));
			}
		}

		return $variableNames;
	}

}
