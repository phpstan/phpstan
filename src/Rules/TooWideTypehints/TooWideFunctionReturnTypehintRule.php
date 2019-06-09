<?php declare(strict_types = 1);

namespace PHPStan\Rules\TooWideTypehints;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FunctionReturnStatementsNode;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class TooWideFunctionReturnTypehintRule implements Rule
{

	public function getNodeType(): string
	{
		return FunctionReturnStatementsNode::class;
	}

	/**
	 * @param \PHPStan\Node\FunctionReturnStatementsNode $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$function = $scope->getFunction();
		if (!$function instanceof FunctionReflection) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$functionReturnType = ParametersAcceptorSelector::selectSingle($function->getVariants())->getReturnType();
		if (!$functionReturnType instanceof UnionType) {
			return [];
		}
		$statementResult = $node->getStatementResult();
		if ($statementResult->hasYield()) {
			return [];
		}

		$returnStatements = $node->getReturnStatements();
		if (count($returnStatements) === 0) {
			return [];
		}

		$returnTypes = [];
		foreach ($returnStatements as $returnStatement) {
			$returnNode = $returnStatement->getReturnNode();
			if ($returnNode->expr === null) {
				continue;
			}

			$returnTypes[] = $returnStatement->getScope()->getType($returnNode->expr);
		}

		if (count($returnTypes) === 0) {
			return [];
		}

		$returnType = TypeCombinator::union(...$returnTypes);

		$messages = [];
		foreach ($functionReturnType->getTypes() as $type) {
			if (!$type->isSuperTypeOf($returnType)->no()) {
				continue;
			}

			$messages[] = sprintf('Function %s() never returns %s so it can be removed from the return typehint.', $function->getName(), $type->describe(VerbosityLevel::typeOnly()));
		}

		return $messages;
	}

}
