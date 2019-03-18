<?php declare(strict_types = 1);

namespace PHPStan\Rules\Missing;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ExecutionEndNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;

class MissingReturnRule implements Rule
{

	public function getNodeType(): string
	{
		return ExecutionEndNode::class;
	}

	/**
	 * @param ExecutionEndNode $node
	 * @param Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$statementResult = $node->getStatementResult();
		if ($statementResult->isAlwaysTerminating()) {
			return [];
		}
		if ($statementResult->hasYield()) {
			return [];
		}

		$originalNode = $node->getNode();
		if (
			(
				($originalNode instanceof Node\Stmt\If_)
				|| ($originalNode instanceof Node\Stmt\ElseIf_)
				|| ($originalNode instanceof Node\Stmt\Else_)
				|| ($originalNode instanceof Node\Stmt\TryCatch)
				|| ($originalNode instanceof Node\Stmt\Catch_)
				|| ($originalNode instanceof Node\Stmt\While_)
				|| ($originalNode instanceof Node\Stmt\Do_)
				|| ($originalNode instanceof Node\Stmt\For_)
				|| ($originalNode instanceof Node\Stmt\Foreach_)
			) && !$this->shouldBeReported($originalNode->stmts)
		) {
			return [];
		}

		// todo pokud neni v try nebo v catches, tak muze byt alespon ve finally

		$anonymousFunctionReturnType = $scope->getAnonymousFunctionReturnType();
		$scopeFunction = $scope->getFunction();
		if ($anonymousFunctionReturnType !== null) {
			$returnType = $anonymousFunctionReturnType;
			$description = 'Anonymous function';
		} elseif ($scopeFunction !== null) {
			$returnType = ParametersAcceptorSelector::selectSingle($scopeFunction->getVariants())->getReturnType();
			if ($scopeFunction instanceof MethodReflection) {
				$description = sprintf('Method %s::%s()', $scopeFunction->getDeclaringClass()->getDisplayName(), $scopeFunction->getName());
			} else {
				$description = sprintf('Function %s()', $scopeFunction->getName());
			}
		} else {
			throw new \PHPStan\ShouldNotHappenException();
		}

		if (
			$returnType instanceof VoidType
			|| ($returnType instanceof MixedType && !$returnType->isExplicitMixed())
		) {
			return [];
		}

		// todo native typehint level 0, phpDocs level 2, explicit mixed level 6
		// todo ignore control flow like If_, Do_, Foreach_... will be inspected deeper

		return [
			RuleErrorBuilder::message(
				sprintf('%s should return %s but return statement is missing.', $description, $returnType->describe(VerbosityLevel::typeOnly()))
			)->line($originalNode->getStartLine())->build(),
		];
	}

	/**
	 * @param Node\Stmt[] $stmts
	 * @return bool
	 */
	private function shouldBeReported(array $stmts): bool
	{
		if (count($stmts) === 0) {
			return true;
		}

		$lastStmt = $stmts[count($stmts) - 1];

		return !$lastStmt instanceof Node\Stmt\If_
			&& !$lastStmt instanceof Node\Stmt\TryCatch
			&& !$lastStmt instanceof Node\Stmt\While_
			&& !$lastStmt instanceof Node\Stmt\Do_
			&& !$lastStmt instanceof Node\Stmt\For_
			&& !$lastStmt instanceof Node\Stmt\Foreach_
			&& !$lastStmt instanceof Node\Stmt\Switch_;
	}

}
