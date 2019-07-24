<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;

class WrongVariableNameInVarTagRule implements Rule
{

	/** @var FileTypeMapper */
	private $fileTypeMapper;

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (
			!$node instanceof Node\Stmt\Foreach_
			&& !$node instanceof Node\Expr\Assign
			&& !$node instanceof Node\Expr\AssignRef
			&& !$node instanceof Node\Stmt\Static_
			&& !$node instanceof Node\Stmt\Echo_
			&& !$node instanceof Node\Stmt\Return_
			&& !$node instanceof Node\Stmt\Expression
			&& !$node instanceof Node\Stmt\Throw_
			&& !$node instanceof Node\Stmt\If_
			&& !$node instanceof Node\Stmt\While_
			&& !$node instanceof Node\Stmt\Switch_
			&& !$node instanceof Node\Stmt\Nop
		) {
			return [];
		}

		$docComment = $node->getDocComment();
		if ($docComment === null) {
			return [];
		}

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$scope->isInClass() ? $scope->getClassReflection()->getName() : null,
			$scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
			$docComment->getText()
		);
		$varTags = $resolvedPhpDoc->getVarTags();
		if (count($varTags) === 0) {
			return [];
		}
		if ($node instanceof Node\Expr\Assign || $node instanceof Node\Expr\AssignRef) {
			return $this->processAssign($scope, $node->var, $varTags);
		}

		if ($node instanceof Node\Stmt\Foreach_) {
			return $this->processForeach($node->keyVar, $node->valueVar, $varTags);
		}

		if ($node instanceof Node\Stmt\Static_) {
			return $this->processStatic($node->vars, $varTags);
		}

		if ($node instanceof Node\Stmt\Expression) {
			return $this->processExpression($scope, $node->expr, $varTags);
		}

		return $this->processStmt($scope, $varTags);
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Expr $var
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @return \PHPStan\Rules\RuleError[]
	 */
	private function processAssign(Scope $scope, Node\Expr $var, array $varTags): array
	{
		if ($var instanceof Node\Expr\Variable && is_string($var->name)) {
			if (count($varTags) === 1) {
				$key = key($varTags);
				if (is_int($key)) {
					return [];
				}

				if ($key !== $var->name) {
					if (!$scope->hasVariableType($key)->no()) {
						return [];
					}

					return [
						RuleErrorBuilder::message(sprintf(
							'Variable $%s in PHPDoc tag @var does not match assigned variable $%s.',
							$key,
							$var->name
						))->build(),
					];
				}

				return [];
			}

			return [
				RuleErrorBuilder::message('Multiple PHPDoc @var tags above single variable assignment are not supported.')->build(),
			];
		}

		return [];
	}

	/**
	 * @param \PhpParser\Node\Expr|null $keyVar
	 * @param \PhpParser\Node\Expr $valueVar
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @return \PHPStan\Rules\RuleError[]
	 */
	private function processForeach(?Node\Expr $keyVar, Node\Expr $valueVar, array $varTags): array
	{
		$variableNames = [];
		if ($keyVar instanceof Node\Expr\Variable && is_string($keyVar->name)) {
			$variableNames[$keyVar->name] = true;
		}
		if ($valueVar instanceof Node\Expr\Variable && is_string($valueVar->name)) {
			$variableNames[$valueVar->name] = true;
		}
		if ($valueVar instanceof Node\Expr\Array_ || $valueVar instanceof Node\Expr\List_) {
			$variableNames = $this->getVariablesFromList($variableNames, $valueVar->items);
		}

		$errors = [];
		foreach (array_keys($varTags) as $name) {
			if (is_int($name)) {
				$errors[] = RuleErrorBuilder::message(
					'PHPDoc tag @var above foreach loop does not specify variable name.'
				)->build();
				continue;
			}

			if (isset($variableNames[$name])) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'Variable $%s in PHPDoc tag @var does not match any variable in the foreach loop: %s',
				$name,
				implode(', ', array_map(static function (string $name): string {
					return sprintf('$%s', $name);
				}, array_keys($variableNames)))
			))->build();
		}

		return $errors;
	}

	/**
	 * @param array<string, true> $variableNames
	 * @param (\PhpParser\Node\Expr\ArrayItem|null)[] $items
	 * @return array<string, true>
	 */
	private function getVariablesFromList(array $variableNames, array $items): array
	{
		foreach ($items as $item) {
			if ($item === null) {
				continue;
			}

			$value = $item->value;

			if ($value instanceof Node\Expr\Variable && is_string($value->name)) {
				$variableNames[$value->name] = true;
				continue;
			}

			if (!($value instanceof Node\Expr\Array_) && !($value instanceof Node\Expr\List_)) {
				continue;
			}

			$variableNames = $this->getVariablesFromList($variableNames, $value->items);
		}

		return $variableNames;
	}

	/**
	 * @param \PhpParser\Node\Stmt\StaticVar[] $vars
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @return \PHPStan\Rules\RuleError[]
	 */
	private function processStatic(array $vars, array $varTags): array
	{
		$variableNames = [];
		foreach ($vars as $var) {
			if (!is_string($var->var->name)) {
				continue;
			}

			$variableNames[$var->var->name] = true;
		}

		$errors = [];
		foreach (array_keys($varTags) as $name) {
			if (is_int($name)) {
				if (count($vars) === 1) {
					continue;
				}

				$errors[] = RuleErrorBuilder::message(
					'PHPDoc tag @var above multiple static variables does not specify variable name.'
				)->build();
				continue;
			}

			if (isset($variableNames[$name])) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'Variable $%s in PHPDoc tag @var does not match any static variable: %s',
				$name,
				implode(', ', array_map(static function (string $name): string {
					return sprintf('$%s', $name);
				}, array_keys($variableNames)))
			))->build();
		}

		return $errors;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Expr $expr
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @return \PHPStan\Rules\RuleError[]
	 */
	private function processExpression(Scope $scope, Expr $expr, array $varTags): array
	{
		if ($expr instanceof Node\Expr\Assign || $expr instanceof Node\Expr\AssignRef) {
			return [];
		}

		return $this->processStmt($scope, $varTags);
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @return \PHPStan\Rules\RuleError[]
	 */
	private function processStmt(Scope $scope, array $varTags): array
	{
		$errors = [];
		foreach (array_keys($varTags) as $name) {
			if (is_int($name)) {
				$errors[] = RuleErrorBuilder::message('PHPDoc tag @var does not specify variable name.')->build();
				continue;
			}

			if (!$scope->hasVariableType($name)->no()) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf('Variable $%s in PHPDoc tag @var does not exist.', $name))->build();
		}

		return $errors;
	}

}
