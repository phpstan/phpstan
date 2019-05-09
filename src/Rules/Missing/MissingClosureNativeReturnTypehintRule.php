<?php declare(strict_types = 1);

namespace PHPStan\Rules\Missing;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClosureReturnStatementsNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class MissingClosureNativeReturnTypehintRule implements Rule
{

	/** @var bool */
	private $checkObjectTypehint;

	public function __construct(bool $checkObjectTypehint)
	{
		$this->checkObjectTypehint = $checkObjectTypehint;
	}

	public function getNodeType(): string
	{
		return ClosureReturnStatementsNode::class;
	}

	/**
	 * @param \PHPStan\Node\ClosureReturnStatementsNode $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$closure = $node->getClosureExpr();
		if ($closure->returnType !== null) {
			return [];
		}

		$messagePattern = 'Anonymous function should have native typehint "%s".';
		$statementResult = $node->getStatementResult();
		if ($statementResult->hasYield()) {
			return [
				RuleErrorBuilder::message(sprintf($messagePattern, 'Generator'))->build(),
			];
		}

		$returnStatements = $node->getReturnStatements();
		if (count($returnStatements) === 0) {
			return [
				RuleErrorBuilder::message(sprintf($messagePattern, 'void'))->build(),
			];
		}

		$returnTypes = [];
		$voidReturnNodes = [];
		$hasNull = false;
		foreach ($returnStatements as $returnStatement) {
			$returnNode = $returnStatement->getReturnNode();
			if ($returnNode->expr === null) {
				$voidReturnNodes[] = $returnNode;
				$hasNull = true;
				continue;
			}

			$returnTypes[] = $returnStatement->getScope()->getType($returnNode->expr);
		}

		if (count($returnTypes) === 0) {
			return [
				RuleErrorBuilder::message(sprintf($messagePattern, 'void'))->build(),
			];
		}

		$messages = [];
		foreach ($voidReturnNodes as $voidReturnStatement) {
			$messages[] = RuleErrorBuilder::message('Mixing returning values with empty return statements - return null should be used here.')
				->line($voidReturnStatement->getLine())
				->build();
		}

		$returnType = TypeCombinator::union(...$returnTypes);
		if (
			$returnType instanceof MixedType
			|| $returnType instanceof NeverType
			|| $returnType instanceof IntersectionType
			|| $returnType instanceof NullType
		) {
			return $messages;
		}

		if (TypeCombinator::containsNull($returnType)) {
			$hasNull = true;
			$returnType = TypeCombinator::removeNull($returnType);
		}

		if (
			$returnType instanceof UnionType
			|| $returnType instanceof ResourceType
		) {
			return $messages;
		}

		if (!$statementResult->isAlwaysTerminating()) {
			$messages[] = RuleErrorBuilder::message('Anonymous function sometimes return something but return statement at the end is missing.')->build();
			return $messages;
		}

		$returnType = TypeUtils::generalizeType($returnType);
		$description = $returnType->describe(VerbosityLevel::typeOnly());
		if ($returnType instanceof ArrayType) {
			$description = 'array';
		}
		if ($hasNull) {
			$description = '?' . $description;
		}

		if (
			!$this->checkObjectTypehint
			&& $returnType instanceof ObjectWithoutClassType
		) {
			return $messages;
		}

		$messages[] = RuleErrorBuilder::message(sprintf($messagePattern, $description))->build();

		return $messages;
	}

}
