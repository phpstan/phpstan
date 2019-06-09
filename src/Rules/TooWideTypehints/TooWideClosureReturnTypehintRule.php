<?php declare(strict_types = 1);

namespace PHPStan\Rules\TooWideTypehints;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClosureReturnStatementsNode;
use PHPStan\Rules\Rule;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class TooWideClosureReturnTypehintRule implements Rule
{

	public function getNodeType(): string
	{
		return ClosureReturnStatementsNode::class;
	}

	/**
	 * @param \PHPStan\Node\ClosureReturnStatementsNode $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$closureReturnType = $scope->getAnonymousFunctionReturnType();
		if ($closureReturnType === null || !$closureReturnType instanceof UnionType) {
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
		foreach ($closureReturnType->getTypes() as $type) {
			if (!$type->isSuperTypeOf($returnType)->no()) {
				continue;
			}

			$messages[] = sprintf('Anonymous function never returns %s so it can be removed from the return typehint.', $type->describe(VerbosityLevel::typeOnly()));
		}

		return $messages;
	}

}
