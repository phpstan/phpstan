<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;

class UnusedFunctionParametersCheck
{

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param string[] $parameterNames
	 * @param \PhpParser\Node[] $statements
	 * @param string $unusedParameterMessage
	 * @return string[]
	 */
	public function getUnusedParameters(
		Scope $scope,
		array $parameterNames,
		array $statements,
		string $unusedParameterMessage
	): array
	{
		$unusedParameters = array_fill_keys($parameterNames, true);
		foreach ($this->getUsedVariables($scope, $statements) as $variableName) {
			if (!isset($unusedParameters[$variableName])) {
				continue;
			}

			unset($unusedParameters[$variableName]);
		}
		$errors = [];
		foreach (array_keys($unusedParameters) as $name) {
			$errors[] = sprintf($unusedParameterMessage, $name);
		}

		return $errors;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node[]|\PhpParser\Node|scalar $node
	 * @return string[]
	 */
	private function getUsedVariables(Scope $scope, $node): array
	{
		$variableNames = [];
		if ($node instanceof Node) {
			if ($node instanceof Node\Expr\Variable && is_string($node->name) && $node->name !== 'this') {
				return [$node->name];
			}
			if ($node instanceof Node\Expr\ClosureUse && is_string($node->var->name)) {
				return [$node->var->name];
			}
			if (
				$node instanceof Node\Expr\FuncCall
				&& $node->name instanceof Node\Name
				&& (string) $node->name === 'compact'
			) {
				foreach ($node->args as $arg) {
					$argType = $scope->getType($arg->value);
					if (!($argType instanceof ConstantStringType)) {
						continue;
					}

					$variableNames[] = $argType->getValue();
				}
			}
			foreach ($node->getSubNodeNames() as $subNodeName) {
				if ($node instanceof Node\Expr\Closure && $subNodeName !== 'uses') {
					continue;
				}
				$subNode = $node->{$subNodeName};
				$variableNames = array_merge($variableNames, $this->getUsedVariables($scope, $subNode));
			}
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$variableNames = array_merge($variableNames, $this->getUsedVariables($scope, $subNode));
			}
		}

		return $variableNames;
	}

}
