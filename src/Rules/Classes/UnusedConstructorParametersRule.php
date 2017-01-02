<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;

class UnusedConstructorParametersRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->name !== '__construct' || $node->stmts === null) {
			return [];
		}

		if (count($node->params) === 0) {
			return [];
		}

		$unusedParameters = [];
		foreach ($node->params as $parameter) {
			$unusedParameters[$parameter->name] = true;
		}
		foreach ($this->getUsedVariables($node->stmts) as $variableName) {
			if (isset($unusedParameters[$variableName])) {
				unset($unusedParameters[$variableName]);
			}
		}

		$errors = [];
		if ($scope->getClass() !== null) {
			$message = sprintf('Constructor of class %s has an unused parameter $%%s.', $scope->getClass());
		} else {
			$message = 'Constructor of an anonymous class has an unused parameter $%s.';
		}
		foreach ($unusedParameters as $name => $bool) {
			$errors[] = sprintf($message, $name);
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
