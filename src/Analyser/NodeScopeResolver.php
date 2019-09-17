<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Broker\Broker;
use PHPStan\File\FileHelper;
use PHPStan\Node\ClosureReturnStatementsNode;
use PHPStan\Node\ExecutionEndNode;
use PHPStan\Node\FunctionReturnStatementsNode;
use PHPStan\Node\InArrowFunctionNode;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Node\LiteralArrayItem;
use PHPStan\Node\LiteralArrayNode;
use PHPStan\Node\MethodReturnStatementsNode;
use PHPStan\Node\ReturnStatement;
use PHPStan\Node\UnreachableStatementNode;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\CommentHelper;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class NodeScopeResolver
{

	private const LOOP_SCOPE_ITERATIONS = 3;
	private const GENERALIZE_AFTER_ITERATION = 1;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	/** @var bool */
	private $polluteScopeWithLoopInitialAssignments;

	/** @var bool */
	private $polluteCatchScopeWithTryAssignments;

	/** @var bool */
	private $polluteScopeWithAlwaysIterableForeach;

	/** @var string[][] className(string) => methods(string[]) */
	private $earlyTerminatingMethodCalls;

	/** @var bool */
	private $allowVarTagAboveStatements;

	/** @var bool[] filePath(string) => bool(true) */
	private $analysedFiles;

	/**
	 * @param Broker $broker
	 * @param Parser $parser
	 * @param FileTypeMapper $fileTypeMapper
	 * @param FileHelper $fileHelper
	 * @param TypeSpecifier $typeSpecifier
	 * @param bool $polluteScopeWithLoopInitialAssignments
	 * @param bool $polluteCatchScopeWithTryAssignments
	 * @param bool $polluteScopeWithAlwaysIterableForeach
	 * @param string[][] $earlyTerminatingMethodCalls className(string) => methods(string[])
	 * @param bool $allowVarTagAboveStatements
	 */
	public function __construct(
		Broker $broker,
		Parser $parser,
		FileTypeMapper $fileTypeMapper,
		FileHelper $fileHelper,
		TypeSpecifier $typeSpecifier,
		bool $polluteScopeWithLoopInitialAssignments,
		bool $polluteCatchScopeWithTryAssignments,
		bool $polluteScopeWithAlwaysIterableForeach,
		array $earlyTerminatingMethodCalls,
		bool $allowVarTagAboveStatements
	)
	{
		$this->broker = $broker;
		$this->parser = $parser;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->fileHelper = $fileHelper;
		$this->typeSpecifier = $typeSpecifier;
		$this->polluteScopeWithLoopInitialAssignments = $polluteScopeWithLoopInitialAssignments;
		$this->polluteCatchScopeWithTryAssignments = $polluteCatchScopeWithTryAssignments;
		$this->polluteScopeWithAlwaysIterableForeach = $polluteScopeWithAlwaysIterableForeach;
		$this->earlyTerminatingMethodCalls = $earlyTerminatingMethodCalls;
		$this->allowVarTagAboveStatements = $allowVarTagAboveStatements;
	}

	/**
	 * @param string[] $files
	 */
	public function setAnalysedFiles(array $files): void
	{
		$this->analysedFiles = array_fill_keys($files, true);
	}

	/**
	 * @param \PhpParser\Node[] $nodes
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 */
	public function processNodes(
		array $nodes,
		Scope $scope,
		\Closure $nodeCallback
	): void
	{
		$nodesCount = count($nodes);
		$alreadyTerminated = false;
		foreach ($nodes as $i => $node) {
			if (!$node instanceof Node\Stmt) {
				continue;
			}

			$statementResult = $this->processStmtNode($node, $scope, $nodeCallback);
			$scope = $statementResult->getScope();
			if ($alreadyTerminated) {
				continue;
			}
			if (!$statementResult->isAlwaysTerminating()) {
				continue;
			}

			$alreadyTerminated = true;
			if ($i < $nodesCount - 1) {
				$nextStmt = $nodes[$i + 1];
				if (!$nextStmt instanceof Node\Stmt) {
					continue;
				}

				$nodeCallback(new UnreachableStatementNode($nextStmt), $scope);
			}
			// todo break;
		}
	}

	/**
	 * @param \PhpParser\Node $parentNode
	 * @param \PhpParser\Node\Stmt[] $stmts
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @return StatementResult
	 */
	public function processStmtNodes(
		Node $parentNode,
		array $stmts,
		Scope $scope,
		\Closure $nodeCallback
	): StatementResult
	{
		$exitPoints = [];
		$alreadyTerminated = false;
		$hasYield = false;
		$stmtCount = count($stmts);
		$shouldCheckLastStatement = $parentNode instanceof Node\Stmt\Function_
			|| $parentNode instanceof Node\Stmt\ClassMethod
			|| $parentNode instanceof Expr\Closure;
		foreach ($stmts as $i => $stmt) {
			$isLast = $i === $stmtCount - 1;
			$statementResult = $this->processStmtNode(
				$stmt,
				$scope,
				$nodeCallback
			);
			$scope = $statementResult->getScope();
			$hasYield = $hasYield || $statementResult->hasYield();

			if ($alreadyTerminated) {
				continue;
			}

			if ($shouldCheckLastStatement && $isLast) {
				/** @var Node\Stmt\Function_|Node\Stmt\ClassMethod|Expr\Closure $parentNode */
				$parentNode = $parentNode;
				$nodeCallback(new ExecutionEndNode(
					$stmt,
					new StatementResult(
						$scope,
						$hasYield,
						$statementResult->isAlwaysTerminating(),
						$statementResult->getExitPoints()
					),
					$parentNode->returnType !== null
				), $scope);
			}

			$exitPoints = array_merge($exitPoints, $statementResult->getExitPoints());

			if (!$statementResult->isAlwaysTerminating()) {
				continue;
			}

			$alreadyTerminated = true;
			if ($i < $stmtCount - 1) {
				$nextStmt = $stmts[$i + 1];
				$nodeCallback(new UnreachableStatementNode($nextStmt), $scope);
			}

			// todo break
		}

		$statementResult = new StatementResult($scope, $hasYield, $alreadyTerminated, $exitPoints);
		if ($stmtCount === 0 && $shouldCheckLastStatement) {
			/** @var Node\Stmt\Function_|Node\Stmt\ClassMethod|Expr\Closure $parentNode */
			$parentNode = $parentNode;
			$nodeCallback(new ExecutionEndNode(
				$parentNode,
				$statementResult,
				$parentNode->returnType !== null
			), $scope);
		}

		return $statementResult;
	}

	/**
	 * @param \PhpParser\Node\Stmt $stmt
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @return StatementResult
	 */
	private function processStmtNode(
		Node\Stmt $stmt,
		Scope $scope,
		\Closure $nodeCallback
	): StatementResult
	{
		$nodeCallback($stmt, $scope);

		if ($stmt instanceof Node\Stmt\Declare_) {
			$hasYield = false;
			foreach ($stmt->declares as $declare) {
				$nodeCallback($declare, $scope);
				$nodeCallback($declare->value, $scope);
				if (
					$declare->key->name !== 'strict_types'
					|| !($declare->value instanceof Node\Scalar\LNumber)
					|| $declare->value->value !== 1
				) {
					continue;
				}

				$scope = $scope->enterDeclareStrictTypes();
			}
		} elseif ($stmt instanceof Node\Stmt\Function_) {
			$hasYield = false;
			[$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $deprecatedDescription, $isDeprecated, $isInternal, $isFinal] = $this->getPhpDocs($scope, $stmt);

			foreach ($stmt->params as $param) {
				$this->processParamNode($param, $scope, $nodeCallback);
			}

			if ($stmt->returnType !== null) {
				$nodeCallback($stmt->returnType, $scope);
			}

			$functionScope = $scope->enterFunction(
				$stmt,
				$phpDocParameterTypes,
				$phpDocReturnType,
				$phpDocThrowType,
				$deprecatedDescription,
				$isDeprecated,
				$isInternal,
				$isFinal
			);

			$gatheredReturnStatements = [];
			$statementResult = $this->processStmtNodes($stmt, $stmt->stmts, $functionScope, static function (\PhpParser\Node $node, Scope $scope) use ($nodeCallback, &$gatheredReturnStatements): void {
				$nodeCallback($node, $scope);
				if (!$node instanceof Return_) {
					return;
				}

				$gatheredReturnStatements[] = new ReturnStatement($scope, $node);
			});

			$nodeCallback(new FunctionReturnStatementsNode(
				$stmt,
				$gatheredReturnStatements,
				$statementResult
			), $functionScope);
		} elseif ($stmt instanceof Node\Stmt\ClassMethod) {
			$hasYield = false;
			[$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $deprecatedDescription, $isDeprecated, $isInternal, $isFinal] = $this->getPhpDocs($scope, $stmt);

			foreach ($stmt->params as $param) {
				$this->processParamNode($param, $scope, $nodeCallback);
			}

			if ($stmt->returnType !== null) {
				$nodeCallback($stmt->returnType, $scope);
			}

			if ($phpDocReturnType !== null) {
				if (!$scope->isInClass()) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				$className = $scope->getClassReflection()->getName();
				$phpDocReturnType = TypeTraverser::map($phpDocReturnType, static function (Type $type, callable $traverse) use ($className): Type {
					if ($type instanceof StaticType) {
						return $traverse($type->changeBaseClass($className));
					}

					return $traverse($type);
				});
			}

			$methodScope = $scope->enterClassMethod(
				$stmt,
				$phpDocParameterTypes,
				$phpDocReturnType,
				$phpDocThrowType,
				$deprecatedDescription,
				$isDeprecated,
				$isInternal,
				$isFinal
			);
			$nodeCallback(new InClassMethodNode($stmt), $methodScope);

			if ($stmt->stmts !== null) {
				$gatheredReturnStatements = [];
				$statementResult = $this->processStmtNodes($stmt, $stmt->stmts, $methodScope, static function (\PhpParser\Node $node, Scope $scope) use ($nodeCallback, &$gatheredReturnStatements): void {
					$nodeCallback($node, $scope);
					if (!$node instanceof Return_) {
						return;
					}

					$gatheredReturnStatements[] = new ReturnStatement($scope, $node);
				});
				$nodeCallback(new MethodReturnStatementsNode(
					$stmt,
					$gatheredReturnStatements,
					$statementResult
				), $methodScope);
			}
		} elseif ($stmt instanceof Echo_) {
			$scope = $this->processStmtVarAnnotation($scope, $stmt);
			$hasYield = false;
			foreach ($stmt->exprs as $echoExpr) {
				$result = $this->processExprNode($echoExpr, $scope, $nodeCallback, ExpressionContext::createDeep());
				$scope = $result->getScope();
				$hasYield = $hasYield || $result->hasYield();
			}
		} elseif ($stmt instanceof Return_) {
			$scope = $this->processStmtVarAnnotation($scope, $stmt);
			if ($stmt->expr !== null) {
				$result = $this->processExprNode($stmt->expr, $scope, $nodeCallback, ExpressionContext::createDeep());
				$scope = $result->getScope();
				$hasYield = $result->hasYield();
			} else {
				$hasYield = false;
			}

			return new StatementResult($scope, $hasYield, true, [
				new StatementExitPoint($stmt, $scope),
			]);
		} elseif ($stmt instanceof Continue_ || $stmt instanceof Break_) {
			if ($stmt->num !== null) {
				$result = $this->processExprNode($stmt->num, $scope, $nodeCallback, ExpressionContext::createDeep());
				$scope = $result->getScope();
				$hasYield = $result->hasYield();
			} else {
				$hasYield = false;
			}

			return new StatementResult($scope, $hasYield, true, [
				new StatementExitPoint($stmt, $scope),
			]);
		} elseif ($stmt instanceof Node\Stmt\Expression) {
			if (!$stmt->expr instanceof Assign && !$stmt->expr instanceof AssignRef) {
				$scope = $this->processStmtVarAnnotation($scope, $stmt);
			}
			$earlyTerminationExpr = $this->findEarlyTerminatingExpr($stmt->expr, $scope);
			$result = $this->processExprNode($stmt->expr, $scope, $nodeCallback, ExpressionContext::createTopLevel());
			$scope = $result->getScope();
			$scope = $scope->filterBySpecifiedTypes($this->typeSpecifier->specifyTypesInCondition(
				$scope,
				$stmt->expr,
				TypeSpecifierContext::createNull()
			));
			$hasYield = $result->hasYield();
			if ($earlyTerminationExpr !== null) {
				return new StatementResult($scope, $hasYield, true, [
					new StatementExitPoint($stmt, $scope),
				]);
			}
		} elseif ($stmt instanceof Node\Stmt\Namespace_) {
			if ($stmt->name !== null) {
				$scope = $scope->enterNamespace($stmt->name->toString());
			}

			$scope = $this->processStmtNodes($stmt, $stmt->stmts, $scope, $nodeCallback)->getScope();
			$hasYield = false;
		} elseif ($stmt instanceof Node\Stmt\Trait_) {
			return new StatementResult($scope, false, false, []);
		} elseif ($stmt instanceof Node\Stmt\ClassLike) {
			$hasYield = false;
			if (isset($stmt->namespacedName)) {
				$classScope = $scope->enterClass($this->broker->getClass((string) $stmt->namespacedName));
			} elseif ($stmt instanceof Class_) {
				if ($stmt->name === null) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$classScope = $scope->enterClass($this->broker->getClass($stmt->name->toString()));
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$this->processStmtNodes($stmt, $stmt->stmts, $classScope, $nodeCallback);
		} elseif ($stmt instanceof Node\Stmt\Property) {
			$hasYield = false;
			foreach ($stmt->props as $prop) {
				$this->processStmtNode($prop, $scope, $nodeCallback);
			}

			if ($stmt->type !== null) {
				$nodeCallback($stmt->type, $scope);
			}
		} elseif ($stmt instanceof Node\Stmt\PropertyProperty) {
			$hasYield = false;
			if ($stmt->default !== null) {
				$this->processExprNode($stmt->default, $scope, $nodeCallback, ExpressionContext::createDeep());
			}
		} elseif ($stmt instanceof Throw_) {
			$scope = $this->processStmtVarAnnotation($scope, $stmt);
			$result = $this->processExprNode($stmt->expr, $scope, $nodeCallback, ExpressionContext::createDeep());
			return new StatementResult($result->getScope(), $result->hasYield(), true, [
				new StatementExitPoint($stmt, $scope),
			]);
		} elseif ($stmt instanceof If_) {
			$scope = $this->processStmtVarAnnotation($scope, $stmt);
			$conditionType = $scope->getType($stmt->cond)->toBoolean();
			$ifAlwaysTrue = $conditionType instanceof ConstantBooleanType && $conditionType->getValue();
			$condResult = $this->processExprNode($stmt->cond, $scope, $nodeCallback, ExpressionContext::createDeep());
			$exitPoints = [];
			$finalScope = null;
			$alwaysTerminating = true;
			$hasYield = false;

			$branchScopeStatementResult = $this->processStmtNodes($stmt, $stmt->stmts, $condResult->getTruthyScope(), $nodeCallback);

			if (!$conditionType instanceof ConstantBooleanType || $conditionType->getValue()) {
				$exitPoints = $branchScopeStatementResult->getExitPoints();
				$branchScope = $branchScopeStatementResult->getScope();
				$finalScope = $branchScopeStatementResult->isAlwaysTerminating() ? null : $branchScope;
				$alwaysTerminating = $branchScopeStatementResult->isAlwaysTerminating();
				$hasYield = $branchScopeStatementResult->hasYield();
			}

			$scope = $condResult->getFalseyScope();
			$lastElseIfConditionIsTrue = false;

			$condScope = $scope;
			foreach ($stmt->elseifs as $elseif) {
				$nodeCallback($elseif, $scope);
				$elseIfConditionType = $condScope->getType($elseif->cond)->toBoolean();
				$condResult = $this->processExprNode($elseif->cond, $condScope, $nodeCallback, ExpressionContext::createDeep());
				$condScope = $condResult->getScope();
				$branchScopeStatementResult = $this->processStmtNodes($elseif, $elseif->stmts, $condResult->getTruthyScope(), $nodeCallback);

				if (
					!$ifAlwaysTrue
					&& (
						!$lastElseIfConditionIsTrue
						&& (
							!$elseIfConditionType instanceof ConstantBooleanType
							|| $elseIfConditionType->getValue()
						)
					)
				) {
					$exitPoints = array_merge($exitPoints, $branchScopeStatementResult->getExitPoints());
					$branchScope = $branchScopeStatementResult->getScope();
					$finalScope = $branchScopeStatementResult->isAlwaysTerminating() ? $finalScope : $branchScope->mergeWith($finalScope);
					$alwaysTerminating = $alwaysTerminating && $branchScopeStatementResult->isAlwaysTerminating();
					$hasYield = $hasYield || $branchScopeStatementResult->hasYield();
				}

				if (
					$elseIfConditionType instanceof ConstantBooleanType
					&& $elseIfConditionType->getValue()
				) {
					$lastElseIfConditionIsTrue = true;
				}

				$condScope = $condScope->filterByFalseyValue($elseif->cond);
				$scope = $condScope;
			}

			if ($stmt->else === null) {
				if (!$ifAlwaysTrue) {
					$finalScope = $scope->mergeWith($finalScope);
					$alwaysTerminating = false;
				}
			} else {
				$nodeCallback($stmt->else, $scope);
				$branchScopeStatementResult = $this->processStmtNodes($stmt->else, $stmt->else->stmts, $scope, $nodeCallback);

				if (!$ifAlwaysTrue && !$lastElseIfConditionIsTrue) {
					$exitPoints = array_merge($exitPoints, $branchScopeStatementResult->getExitPoints());
					$branchScope = $branchScopeStatementResult->getScope();
					$finalScope = $branchScopeStatementResult->isAlwaysTerminating() ? $finalScope : $branchScope->mergeWith($finalScope);
					$alwaysTerminating = $alwaysTerminating && $branchScopeStatementResult->isAlwaysTerminating();
					$hasYield = $hasYield || $branchScopeStatementResult->hasYield();
				}
			}

			if ($finalScope === null) {
				$finalScope = $scope;
			}

			return new StatementResult($finalScope, $hasYield, $alwaysTerminating, $exitPoints);
		} elseif ($stmt instanceof Node\Stmt\TraitUse) {
			$hasYield = false;
			$this->processTraitUse($stmt, $scope, $nodeCallback);
		} elseif ($stmt instanceof Foreach_) {
			$scope = $this->processExprNode($stmt->expr, $scope, $nodeCallback, ExpressionContext::createDeep())->getScope();
			$bodyScope = $this->enterForeach($scope, $stmt);
			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScope = $bodyScope->mergeWith($scope);
				$bodyScope = $this->enterForeach($bodyScope, $stmt);
				$bodyScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopExitPoints();
				$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
				$bodyScope = $bodyScopeResult->getScope();
				foreach ($bodyScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
					$bodyScope = $bodyScope->mergeWith($continueExitPoint->getScope());
				}
				if ($bodyScope->equals($prevScope)) {
					break;
				}

				if ($count >= self::GENERALIZE_AFTER_ITERATION) {
					$bodyScope = $bodyScope->generalizeWith($prevScope);
				}
				$count++;
			} while (!$alwaysTerminating && $count < self::LOOP_SCOPE_ITERATIONS);

			$bodyScope = $bodyScope->mergeWith($scope);
			$bodyScope = $this->enterForeach($bodyScope, $stmt);
			$finalScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, $nodeCallback)->filterOutLoopExitPoints();
			$finalScope = $finalScopeResult->getScope();
			foreach ($finalScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
				$finalScope = $continueExitPoint->getScope()->mergeWith($finalScope);
			}
			foreach ($finalScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
				$finalScope = $breakExitPoint->getScope()->mergeWith($finalScope);
			}

			$isIterableAtLeastOnce = $scope->getType($stmt->expr)->isIterableAtLeastOnce();
			if ($isIterableAtLeastOnce->no() || $finalScopeResult->isAlwaysTerminating()) {
				$finalScope = $scope;
			} elseif ($isIterableAtLeastOnce->maybe()) {
				$finalScope = $finalScope->mergeWith($scope);
			} elseif (!$this->polluteScopeWithAlwaysIterableForeach) {
				$finalScope = $scope->processAlwaysIterableForeachScopeWithoutPollute($finalScope);
				// get types from finalScope, but don't create new variables
			}

			return new StatementResult(
				$finalScope,
				$finalScopeResult->hasYield(),
				$isIterableAtLeastOnce->yes() && $finalScopeResult->isAlwaysTerminating(),
				[]
			);
		} elseif ($stmt instanceof While_) {
			$scope = $this->processStmtVarAnnotation($scope, $stmt);
			$condResult = $this->processExprNode($stmt->cond, $scope, static function (): void {
			}, ExpressionContext::createDeep());
			$bodyScope = $condResult->getTruthyScope();
			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScope = $bodyScope->mergeWith($scope);
				$bodyScope = $this->processExprNode($stmt->cond, $bodyScope, static function (): void {
				}, ExpressionContext::createDeep())->getTruthyScope();
				$bodyScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopExitPoints();
				$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
				$bodyScope = $bodyScopeResult->getScope();
				foreach ($bodyScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
					$bodyScope = $bodyScope->mergeWith($continueExitPoint->getScope());
				}
				if ($bodyScope->equals($prevScope)) {
					break;
				}

				if ($count >= self::GENERALIZE_AFTER_ITERATION) {
					$bodyScope = $bodyScope->generalizeWith($prevScope);
				}
				$count++;
			} while (!$alwaysTerminating && $count < self::LOOP_SCOPE_ITERATIONS);

			$bodyScope = $bodyScope->mergeWith($scope);
			$bodyScopeMaybeRan = $bodyScope;
			$bodyScope = $this->processExprNode($stmt->cond, $bodyScope, $nodeCallback, ExpressionContext::createDeep())->getTruthyScope();
			$finalScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, $nodeCallback)->filterOutLoopExitPoints();
			$finalScope = $finalScopeResult->getScope();
			foreach ($finalScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
				$finalScope = $finalScope->mergeWith($continueExitPoint->getScope());
			}
			$breakExitPoints = $finalScopeResult->getExitPointsByType(Break_::class);
			foreach ($breakExitPoints as $breakExitPoint) {
				$finalScope = $finalScope->mergeWith($breakExitPoint->getScope());
			}

			$beforeCondBooleanType = $scope->getType($stmt->cond)->toBoolean();
			$condBooleanType = $bodyScopeMaybeRan->getType($stmt->cond)->toBoolean();
			$isIterableAtLeastOnce = $beforeCondBooleanType instanceof ConstantBooleanType && $beforeCondBooleanType->getValue();
			$alwaysIterates = $condBooleanType instanceof ConstantBooleanType && $condBooleanType->getValue();

			if ($alwaysIterates) {
				$isAlwaysTerminating = count($finalScopeResult->getExitPointsByType(Break_::class)) === 0;
			} elseif ($isIterableAtLeastOnce) {
				$isAlwaysTerminating = $finalScopeResult->isAlwaysTerminating();
			} else {
				$isAlwaysTerminating = false;
			}
			// todo for all loops - is not falsey when the loop is exited via break
			$condScope = $condResult->getFalseyScope();
			if (!$isIterableAtLeastOnce) {
				if (!$this->polluteScopeWithLoopInitialAssignments) {
					$condScope = $condScope->mergeWith($scope);
				}
				$finalScope = $finalScope->mergeWith($condScope);
			}

			return new StatementResult(
				$finalScope,
				$finalScopeResult->hasYield(),
				$isAlwaysTerminating,
				[]
			);
		} elseif ($stmt instanceof Do_) {
			$finalScope = null;
			$bodyScope = $scope;
			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopExitPoints();
				$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
				$bodyScope = $bodyScopeResult->getScope();
				foreach ($bodyScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
					$bodyScope = $bodyScope->mergeWith($continueExitPoint->getScope());
				}
				$finalScope = $alwaysTerminating ? $finalScope : $bodyScope->mergeWith($finalScope);
				foreach ($bodyScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
					$finalScope = $breakExitPoint->getScope()->mergeWith($finalScope);
				}
				$bodyScope = $this->processExprNode($stmt->cond, $bodyScope, static function (): void {
				}, ExpressionContext::createDeep())->getTruthyScope();
				if ($bodyScope->equals($prevScope)) {
					break;
				}

				if ($count >= self::GENERALIZE_AFTER_ITERATION) {
					$bodyScope = $bodyScope->generalizeWith($prevScope);
				}
				$count++;
			} while (!$alwaysTerminating && $count < self::LOOP_SCOPE_ITERATIONS);

			$bodyScope = $bodyScope->mergeWith($scope);

			$bodyScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, $nodeCallback)->filterOutLoopExitPoints();
			$bodyScope = $bodyScopeResult->getScope();
			$condBooleanType = $bodyScope->getType($stmt->cond)->toBoolean();
			$alwaysIterates = $condBooleanType instanceof ConstantBooleanType && $condBooleanType->getValue();

			if ($alwaysIterates) {
				$alwaysTerminating = count($bodyScopeResult->getExitPointsByType(Break_::class)) === 0;
			} else {
				$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
			}
			foreach ($bodyScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
				$bodyScope = $bodyScope->mergeWith($continueExitPoint->getScope());
			}
			$finalScope = $alwaysTerminating ? $finalScope : $bodyScope->mergeWith($finalScope);
			if ($finalScope === null) {
				$finalScope = $scope;
			}
			if (!$alwaysTerminating) {
				$finalScope = $this->processExprNode($stmt->cond, $bodyScope, $nodeCallback, ExpressionContext::createDeep())->getFalseyScope();
				// todo not falsey if it breaks out of the loop using break;
			}
			foreach ($bodyScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
				$finalScope = $breakExitPoint->getScope()->mergeWith($finalScope);
			}

			return new StatementResult($finalScope, $bodyScopeResult->hasYield(), $alwaysTerminating, []);
		} elseif ($stmt instanceof For_) {
			$initScope = $scope;
			foreach ($stmt->init as $initExpr) {
				$initScope = $this->processExprNode($initExpr, $initScope, $nodeCallback, ExpressionContext::createTopLevel())->getScope();
			}

			$bodyScope = $initScope;
			foreach ($stmt->cond as $condExpr) {
				$bodyScope = $this->processExprNode($condExpr, $bodyScope, static function (): void {
				}, ExpressionContext::createDeep())->getTruthyScope();
			}

			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScope = $bodyScope->mergeWith($initScope);
				foreach ($stmt->cond as $condExpr) {
					$bodyScope = $this->processExprNode($condExpr, $bodyScope, static function (): void {
					}, ExpressionContext::createDeep())->getTruthyScope();
				}
				$bodyScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopExitPoints();
				$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
				$bodyScope = $bodyScopeResult->getScope();
				foreach ($bodyScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
					$bodyScope = $bodyScope->mergeWith($continueExitPoint->getScope());
				}
				foreach ($stmt->loop as $loopExpr) {
					$bodyScope = $this->processExprNode($loopExpr, $bodyScope, static function (): void {
					}, ExpressionContext::createTopLevel())->getScope();
				}

				if ($bodyScope->equals($prevScope)) {
					break;
				}

				if ($count >= self::GENERALIZE_AFTER_ITERATION) {
					$bodyScope = $bodyScope->generalizeWith($prevScope);
				}
				$count++;
			} while (!$alwaysTerminating && $count < self::LOOP_SCOPE_ITERATIONS);

			$bodyScope = $bodyScope->mergeWith($initScope);
			foreach ($stmt->cond as $condExpr) {
				$bodyScope = $this->processExprNode($condExpr, $bodyScope, $nodeCallback, ExpressionContext::createDeep())->getTruthyScope();
			}

			$finalScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $bodyScope, $nodeCallback)->filterOutLoopExitPoints();
			$finalScope = $finalScopeResult->getScope();
			foreach ($finalScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
				$finalScope = $continueExitPoint->getScope()->mergeWith($finalScope);
			}
			foreach ($stmt->loop as $loopExpr) {
				$finalScope = $this->processExprNode($loopExpr, $finalScope, $nodeCallback, ExpressionContext::createTopLevel())->getScope();
			}
			foreach ($finalScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
				$finalScope = $breakExitPoint->getScope()->mergeWith($finalScope);
			}

			if ($this->polluteScopeWithLoopInitialAssignments) {
				$scope = $initScope;
			}

			$finalScope = $finalScope->mergeWith($scope);

			/*foreach ($stmt->cond as $condExpr) {
				// todo not if breaks out of the loop using break;
				//$finalScope = $finalScope->filterByFalseyValue($condExpr);
			}*/

			return new StatementResult($finalScope, $finalScopeResult->hasYield(), false/* $finalScopeResult->isAlwaysTerminating() && $isAlwaysIterable*/, []);
		} elseif ($stmt instanceof Switch_) {
			$scope = $this->processStmtVarAnnotation($scope, $stmt);
			$scope = $this->processExprNode($stmt->cond, $scope, $nodeCallback, ExpressionContext::createDeep())->getScope();
			$scopeForBranches = $scope;
			$finalScope = null;
			$prevScope = null;
			$hasDefaultCase = false;
			$alwaysTerminating = true;
			$hasYield = false;
			foreach ($stmt->cases as $caseNode) {
				if ($caseNode->cond !== null) {
					$condExpr = new BinaryOp\Equal($stmt->cond, $caseNode->cond);
					$scopeForBranches = $this->processExprNode($caseNode->cond, $scopeForBranches, $nodeCallback, ExpressionContext::createDeep())->getScope();
					$branchScope = $scopeForBranches->filterByTruthyValue($condExpr);
				} else {
					$hasDefaultCase = true;
					$branchScope = $scopeForBranches;
				}

				$branchScope = $branchScope->mergeWith($prevScope);
				$branchScopeResult = $this->processStmtNodes($caseNode, $caseNode->stmts, $branchScope, $nodeCallback);
				$branchScope = $branchScopeResult->getScope();
				$branchFinalScopeResult = $branchScopeResult->filterOutLoopExitPoints();
				$hasYield = $hasYield || $branchFinalScopeResult->hasYield();
				foreach ($branchScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
					$finalScope = $breakExitPoint->getScope()->mergeWith($finalScope);
				}
				foreach ($branchScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
					$finalScope = $continueExitPoint->getScope()->mergeWith($finalScope);
				}
				if ($branchScopeResult->isAlwaysTerminating()) {
					$alwaysTerminating = $alwaysTerminating && $branchFinalScopeResult->isAlwaysTerminating();
					$prevScope = null;
					if (isset($condExpr)) {
						$scopeForBranches = $scopeForBranches->filterByFalseyValue($condExpr);
					}
					if (!$branchFinalScopeResult->isAlwaysTerminating()) {
						$finalScope = $branchScope->mergeWith($finalScope);
					}
				} else {
					$prevScope = $branchScope;
				}
			}

			if (!$hasDefaultCase) {
				$alwaysTerminating = false;
			}

			if ($prevScope !== null && isset($branchFinalScopeResult)) {
				$finalScope = $prevScope->mergeWith($finalScope);
				$alwaysTerminating = $alwaysTerminating && $branchFinalScopeResult->isAlwaysTerminating();
			}

			if (!$hasDefaultCase || $finalScope === null) {
				$finalScope = $scope->mergeWith($finalScope);
			}

			return new StatementResult($finalScope, $hasYield, $alwaysTerminating, []);
		} elseif ($stmt instanceof TryCatch) {
			$branchScopeResult = $this->processStmtNodes($stmt, $stmt->stmts, $scope, $nodeCallback);
			$branchScope = $branchScopeResult->getScope();
			$tryScope = $branchScope;
			$exitPoints = [];
			$finalScope = $branchScopeResult->isAlwaysTerminating() ? null : $branchScope;
			$alwaysTerminating = $branchScopeResult->isAlwaysTerminating();
			$hasYield = $branchScopeResult->hasYield();

			if ($stmt->finally !== null) {
				$finallyScope = $branchScope;
			} else {
				$finallyScope = null;
			}
			foreach ($branchScopeResult->getExitPoints() as $exitPoint) {
				if ($finallyScope !== null) {
					$finallyScope = $finallyScope->mergeWith($exitPoint->getScope());
				}
				$exitPoints[] = $exitPoint;
			}

			foreach ($stmt->catches as $catchNode) {
				$nodeCallback($catchNode, $scope);
				if (!is_string($catchNode->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				if (!$this->polluteCatchScopeWithTryAssignments) {
					$catchScopeResult = $this->processCatchNode($catchNode, $scope->mergeWith($tryScope), $nodeCallback);
					$catchScopeForFinally = $catchScopeResult->getScope();
				} else {
					$catchScopeForFinally = $this->processCatchNode($catchNode, $tryScope, $nodeCallback)->getScope();
					$catchScopeResult = $this->processCatchNode($catchNode, $scope->mergeWith($tryScope), static function (): void {
					});
				}

				$finalScope = $catchScopeResult->isAlwaysTerminating() ? $finalScope : $catchScopeResult->getScope()->mergeWith($finalScope);
				$alwaysTerminating = $alwaysTerminating && $catchScopeResult->isAlwaysTerminating();
				$hasYield = $hasYield || $catchScopeResult->hasYield();

				if ($finallyScope !== null) {
					$finallyScope = $finallyScope->mergeWith($catchScopeForFinally);
				}
				foreach ($catchScopeResult->getExitPoints() as $exitPoint) {
					if ($finallyScope !== null) {
						$finallyScope = $finallyScope->mergeWith($exitPoint->getScope());
					}
					$exitPoints[] = $exitPoint;
				}
			}

			if ($finalScope === null) {
				$finalScope = $scope;
			}

			if ($finallyScope !== null && $stmt->finally !== null) {
				$originalFinallyScope = $finallyScope;
				$finallyResult = $this->processStmtNodes($stmt->finally, $stmt->finally->stmts, $finallyScope, $nodeCallback);
				$alwaysTerminating = $alwaysTerminating || $finallyResult->isAlwaysTerminating();
				$hasYield = $hasYield || $finallyResult->hasYield();
				$finallyScope = $finallyResult->getScope();
				$finalScope = $finallyResult->isAlwaysTerminating() ? $finalScope : $finalScope->processFinallyScope($finallyScope, $originalFinallyScope);
				$exitPoints = array_merge($exitPoints, $finallyResult->getExitPoints());
			}

			return new StatementResult($finalScope, $hasYield, $alwaysTerminating, $exitPoints);
		} elseif ($stmt instanceof Unset_) {
			$hasYield = false;
			foreach ($stmt->vars as $var) {
				$scope = $this->lookForEnterVariableAssign($scope, $var);
				$scope = $this->processExprNode($var, $scope, $nodeCallback, ExpressionContext::createDeep())->getScope();
				$scope = $this->lookForExitVariableAssign($scope, $var);
				$scope = $scope->unsetExpression($var);
			}
		} elseif ($stmt instanceof Node\Stmt\Use_) {
			$hasYield = false;
			foreach ($stmt->uses as $use) {
				$this->processStmtNode($use, $scope, $nodeCallback);
			}
		} elseif ($stmt instanceof Node\Stmt\Global_) {
			$hasYield = false;
			foreach ($stmt->vars as $var) {
				if (!$var instanceof Variable) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$scope = $this->lookForEnterVariableAssign($scope, $var);
				$this->processExprNode($var, $scope, $nodeCallback, ExpressionContext::createDeep());
				$scope = $this->lookForExitVariableAssign($scope, $var);

				if (!is_string($var->name)) {
					continue;
				}

				$scope = $scope->assignVariable($var->name, new MixedType());
			}
		} elseif ($stmt instanceof Static_) {
			$hasYield = false;
			$comment = CommentHelper::getDocComment($stmt);
			foreach ($stmt->vars as $var) {
				$scope = $this->processStmtNode($var, $scope, $nodeCallback)->getScope();
				if ($comment === null || !is_string($var->var->name)) {
					continue;
				}
				$scope = $this->processVarAnnotation($scope, $var->var->name, $comment, false);
			}
		} elseif ($stmt instanceof StaticVar) {
			$hasYield = false;
			if (!is_string($stmt->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if ($stmt->default !== null) {
				$this->processExprNode($stmt->default, $scope, $nodeCallback, ExpressionContext::createDeep());
			}
			$scope = $scope->enterExpressionAssign($stmt->var);
			$this->processExprNode($stmt->var, $scope, $nodeCallback, ExpressionContext::createDeep());
			$scope = $scope->exitExpressionAssign($stmt->var);
			$scope = $scope->assignVariable($stmt->var->name, new MixedType());
		} elseif ($stmt instanceof Node\Stmt\Const_ || $stmt instanceof Node\Stmt\ClassConst) {
			$hasYield = false;
			foreach ($stmt->consts as $const) {
				$nodeCallback($const, $scope);
				$this->processExprNode($const->value, $scope, $nodeCallback, ExpressionContext::createDeep());
				$scope = $scope->specifyExpressionType(new ConstFetch(new Name\FullyQualified($const->name->toString())), $scope->getType($const->value));
			}
		} elseif ($stmt instanceof Node\Stmt\Nop) {
			$scope = $this->processStmtVarAnnotation($scope, $stmt);
			$hasYield = false;
		} else {
			$hasYield = false;
		}

		return new StatementResult($scope, $hasYield, false, []);
	}

	/**
	 * @param Node\Stmt\Catch_ $catchNode
	 * @param Scope $catchScope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @return StatementResult
	 */
	private function processCatchNode(
		Node\Stmt\Catch_ $catchNode,
		Scope $catchScope,
		\Closure $nodeCallback
	): StatementResult
	{
		if (!is_string($catchNode->var->name)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$catchScope = $catchScope->enterCatch($catchNode->types, $catchNode->var->name);
		return $this->processStmtNodes($catchNode, $catchNode->stmts, $catchScope, $nodeCallback);
	}

	private function lookForEnterVariableAssign(Scope $scope, Expr $expr): Scope
	{
		if (!$expr instanceof ArrayDimFetch || $expr->dim !== null) {
			$scope = $scope->enterExpressionAssign($expr);
		}
		if (!$expr instanceof Variable) {
			return $this->lookForVariableAssignCallback($scope, $expr, static function (Scope $scope, Expr $expr): Scope {
				return $scope->enterExpressionAssign($expr);
			});
		}

		return $scope;
	}

	private function lookForExitVariableAssign(Scope $scope, Expr $expr): Scope
	{
		$scope = $scope->exitExpressionAssign($expr);
		if (!$expr instanceof Variable) {
			return $this->lookForVariableAssignCallback($scope, $expr, static function (Scope $scope, Expr $expr): Scope {
				return $scope->exitExpressionAssign($expr);
			});
		}

		return $scope;
	}

	/**
	 * @param Scope $scope
	 * @param Expr $expr
	 * @param \Closure(Scope $scope, Expr $expr): Scope $callback
	 * @return Scope
	 */
	private function lookForVariableAssignCallback(Scope $scope, Expr $expr, \Closure $callback): Scope
	{
		if ($expr instanceof Variable) {
			$scope = $callback($scope, $expr);
		} elseif ($expr instanceof ArrayDimFetch) {
			while ($expr instanceof ArrayDimFetch) {
				$expr = $expr->var;
			}

			$scope = $this->lookForVariableAssignCallback($scope, $expr, $callback);
		} elseif ($expr instanceof PropertyFetch) {
			$scope = $this->lookForVariableAssignCallback($scope, $expr->var, $callback);
		} elseif ($expr instanceof StaticPropertyFetch) {
			if ($expr->class instanceof Expr) {
				$scope = $this->lookForVariableAssignCallback($scope, $expr->class, $callback);
			}
		} elseif ($expr instanceof Array_ || $expr instanceof List_) {
			foreach ($expr->items as $item) {
				if ($item === null) {
					continue;
				}

				$scope = $this->lookForVariableAssignCallback($scope, $item->value, $callback);
			}
		}

		return $scope;
	}

	private function ensureNonNullability(Scope $scope, Expr $expr, bool $findMethods): EnsuredNonNullabilityResult
	{
		$exprToSpecify = $expr;
		$specifiedExpressions = [];
		while (
			$exprToSpecify instanceof PropertyFetch
			|| $exprToSpecify instanceof StaticPropertyFetch
			|| (
				$findMethods && (
					$exprToSpecify instanceof MethodCall
					|| $exprToSpecify instanceof StaticCall
				)
			)
		) {
			if (
				$exprToSpecify instanceof PropertyFetch
				|| $exprToSpecify instanceof MethodCall
			) {
				$exprToSpecify = $exprToSpecify->var;
			} elseif ($exprToSpecify->class instanceof Expr) {
				$exprToSpecify = $exprToSpecify->class;
			} else {
				break;
			}

			$exprType = $scope->getType($exprToSpecify);
			$exprTypeWithoutNull = TypeCombinator::removeNull($exprType);
			if ($exprType->equals($exprTypeWithoutNull)) {
				continue;
			}

			$specifiedExpressions[] = new EnsuredNonNullabilityResultExpression($exprToSpecify, $exprType);

			$scope = $scope->specifyExpressionType($exprToSpecify, $exprTypeWithoutNull);
		}

		return new EnsuredNonNullabilityResult($scope, $specifiedExpressions);
	}

	/**
	 * @param Scope $scope
	 * @param EnsuredNonNullabilityResultExpression[] $specifiedExpressions
	 * @return Scope
	 */
	private function revertNonNullability(Scope $scope, array $specifiedExpressions): Scope
	{
		foreach ($specifiedExpressions as $specifiedExpressionResult) {
			$scope = $scope->specifyExpressionType($specifiedExpressionResult->getExpression(), $specifiedExpressionResult->getOriginalType());
		}

		return $scope;
	}

	private function findEarlyTerminatingExpr(Expr $expr, Scope $scope): ?Expr
	{
		if (($expr instanceof MethodCall || $expr instanceof Expr\StaticCall) && count($this->earlyTerminatingMethodCalls) > 0) {
			if ($expr->name instanceof Expr) {
				return null;
			}

			if ($expr instanceof MethodCall) {
				$methodCalledOnType = $scope->getType($expr->var);
			} else {
				if ($expr->class instanceof Name) {
					$methodCalledOnType = $scope->getFunctionType($expr->class, false, false);
				} else {
					$methodCalledOnType = $scope->getType($expr->class);
				}
			}

			$directClassNames = TypeUtils::getDirectClassNames($methodCalledOnType);
			foreach ($directClassNames as $referencedClass) {
				if (!$this->broker->hasClass($referencedClass)) {
					continue;
				}

				$classReflection = $this->broker->getClass($referencedClass);
				foreach (array_merge([$referencedClass], $classReflection->getParentClassesNames(), $classReflection->getNativeReflection()->getInterfaceNames()) as $className) {
					if (!isset($this->earlyTerminatingMethodCalls[$className])) {
						continue;
					}

					if (in_array((string) $expr->name, $this->earlyTerminatingMethodCalls[$className], true)) {
						return $expr;
					}
				}
			}
		}

		if ($expr instanceof Exit_) {
			return $expr;
		}

		return null;
	}

	/**
	 * @param \PhpParser\Node\Expr $expr
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param \PHPStan\Analyser\ExpressionContext $context
	 * @return \PHPStan\Analyser\ExpressionResult
	 */
	private function processExprNode(Expr $expr, Scope $scope, \Closure $nodeCallback, ExpressionContext $context): ExpressionResult
	{
		$this->callNodeCallbackWithExpression($nodeCallback, $expr, $scope, $context);

		if ($expr instanceof Variable) {
			$hasYield = false;
			if ($expr->name instanceof Expr) {
				return $this->processExprNode($expr->name, $scope, $nodeCallback, $context->enterDeep());
			}
		} elseif ($expr instanceof Assign || $expr instanceof AssignRef) {
			if (!$expr->var instanceof Array_ && !$expr->var instanceof List_) {
				$result = $this->processAssignVar(
					$scope,
					$expr->var,
					$expr->expr,
					$nodeCallback,
					$context,
					function (Scope $scope) use ($expr, $nodeCallback, $context): ExpressionResult {
						$hasYield = false;
						if ($expr instanceof AssignRef) {
							$scope = $scope->enterExpressionAssign($expr->expr);
						}

						if ($expr->var instanceof Variable && is_string($expr->var->name)) {
							$context = $context->enterRightSideAssign(
								$expr->var->name,
								$scope->getType($expr->expr)
							);
						}

						$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
						$hasYield = $result->hasYield();
						$scope = $result->getScope();

						if ($expr instanceof AssignRef) {
							$scope = $scope->exitExpressionAssign($expr->expr);
						}

						return new ExpressionResult($scope, $hasYield);
					},
					true
				);
				$scope = $result->getScope();
				$hasYield = $result->hasYield();
				$varChangedScope = false;
				if ($expr->var instanceof Variable && is_string($expr->var->name)) {
					$comment = CommentHelper::getDocComment($expr);
					if ($comment !== null) {
						$scope = $this->processVarAnnotation($scope, $expr->var->name, $comment, false, $varChangedScope);
					}
				}

				if (!$varChangedScope) {
					$scope = $this->processStmtVarAnnotation($scope, new Node\Stmt\Expression($expr, [
						'comments' => $expr->getAttribute('comments'),
					]));
				}
			} else {
				$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $result->hasYield();
				$scope = $result->getScope();
				foreach ($expr->var->items as $arrayItem) {
					if ($arrayItem === null) {
						continue;
					}

					$itemScope = $scope;
					if ($arrayItem->value instanceof ArrayDimFetch && $arrayItem->value->dim === null) {
						$itemScope = $itemScope->enterExpressionAssign($arrayItem->value);
					}
					$itemScope = $this->lookForEnterVariableAssign($itemScope, $arrayItem->value);

					$this->processExprNode($arrayItem, $itemScope, $nodeCallback, $context->enterDeep());
				}
				$scope = $this->lookForArrayDestructuringArray($scope, $expr->var, $scope->getType($expr->expr));
				$comment = CommentHelper::getDocComment($expr);
				if ($comment !== null) {
					foreach ($expr->var->items as $arrayItem) {
						if ($arrayItem === null) {
							continue;
						}
						if (!$arrayItem->value instanceof Variable || !is_string($arrayItem->value->name)) {
							continue;
						}

						$varChangedScope = false;
						$scope = $this->processVarAnnotation($scope, $arrayItem->value->name, $comment, true, $varChangedScope);
						if ($varChangedScope) {
							continue;
						}

						$scope = $this->processStmtVarAnnotation($scope, new Node\Stmt\Expression($expr, [
							'comments' => $expr->getAttribute('comments'),
						]));
					}
				}
			}
		} elseif ($expr instanceof Expr\AssignOp) {
			$result = $this->processAssignVar(
				$scope,
				$expr->var,
				$expr,
				$nodeCallback,
				$context,
				function (Scope $scope) use ($expr, $nodeCallback, $context): ExpressionResult {
					return $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
				},
				$expr instanceof Expr\AssignOp\Coalesce
			);
			$scope = $result->getScope();
			$hasYield = $result->hasYield();
		} elseif ($expr instanceof FuncCall) {
			$parametersAcceptor = null;
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $context->enterDeep())->getScope();
			} elseif ($this->broker->hasFunction($expr->name, $scope)) {
				$function = $this->broker->getFunction($expr->name, $scope);
				$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
					$scope,
					$expr->args,
					$function->getVariants()
				);
			}
			$result = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $context);
			$scope = $result->getScope();
			$hasYield = $result->hasYield();
			if (
				isset($function)
				&& in_array($function->getName(), ['array_pop', 'array_shift'], true)
				&& count($expr->args) >= 1
			) {
				$arrayArg = $expr->args[0]->value;
				$constantArrays = TypeUtils::getConstantArrays($scope->getType($arrayArg));
				if (count($constantArrays) > 0) {
					$resultArrayTypes = [];

					foreach ($constantArrays as $constantArray) {
						if ($function->getName() === 'array_pop') {
							$resultArrayTypes[] = $constantArray->removeLast();
						} else {
							$resultArrayTypes[] = $constantArray->removeFirst();
						}
					}

					$scope = $scope->specifyExpressionType(
						$arrayArg,
						TypeCombinator::union(...$resultArrayTypes)
					);
				} else {
					$arrays = TypeUtils::getAnyArrays($scope->getType($arrayArg));
					if (count($arrays) > 0) {
						$scope = $scope->specifyExpressionType($arrayArg, TypeCombinator::union(...$arrays));
					}
				}
			}

			if (
				isset($function)
				&& in_array($function->getName(), ['array_push', 'array_unshift'], true)
				&& count($expr->args) >= 2
			) {
				$argumentTypes = [];
				foreach (array_slice($expr->args, 1) as $callArg) {
					$callArgType = $scope->getType($callArg->value);
					if ($callArg->unpack) {
						$iterableValueType = $callArgType->getIterableValueType();
						if ($iterableValueType instanceof UnionType) {
							foreach ($iterableValueType->getTypes() as $innerType) {
								$argumentTypes[] = $innerType;
							}
						} else {
							$argumentTypes[] = $iterableValueType;
						}
						continue;
					}

					$argumentTypes[] = $callArgType;
				}

				$arrayArg = $expr->args[0]->value;
				$originalArrayType = $scope->getType($arrayArg);
				$constantArrays = TypeUtils::getConstantArrays($originalArrayType);
				if (
					$function->getName() === 'array_push'
					|| ($originalArrayType->isArray()->yes() && count($constantArrays) === 0)
				) {
					$arrayType = $originalArrayType;
					foreach ($argumentTypes as $argType) {
						$arrayType = $arrayType->setOffsetValueType(null, $argType);
					}

					$scope = $scope->specifyExpressionType($arrayArg, TypeCombinator::intersect($arrayType, new NonEmptyArrayType()));
				} elseif (count($constantArrays) > 0) {
					$defaultArrayBuilder = ConstantArrayTypeBuilder::createEmpty();
					foreach ($argumentTypes as $argType) {
						$defaultArrayBuilder->setOffsetValueType(null, $argType);
					}

					$defaultArrayType = $defaultArrayBuilder->getArray();

					$arrayTypes = [];
					foreach ($constantArrays as $constantArray) {
						$arrayType = $defaultArrayType;
						foreach ($constantArray->getKeyTypes() as $i => $keyType) {
							$valueType = $constantArray->getValueTypes()[$i];
							if ($keyType instanceof ConstantIntegerType) {
								$keyType = null;
							}
							$arrayType = $arrayType->setOffsetValueType($keyType, $valueType);
						}
						$arrayTypes[] = $arrayType;
					}

					$scope = $scope->specifyExpressionType(
						$arrayArg,
						TypeCombinator::union(...$arrayTypes)
					);
				}
			}

			if (
				isset($function)
				&& in_array($function->getName(), ['fopen', 'file_get_contents'], true)
			) {
				$scope = $scope->assignVariable('http_response_header', new ArrayType(new IntegerType(), new StringType()));
			}
		} elseif ($expr instanceof MethodCall) {
			$originalScope = $scope;
			if (
				($expr->var instanceof Expr\Closure || $expr->var instanceof Expr\ArrowFunction)
				&& $expr->name instanceof Node\Identifier
				&& strtolower($expr->name->name) === 'call'
				&& isset($expr->args[0])
			) {
				$closureCallScope = $scope->enterClosureCall($scope->getType($expr->args[0]->value));
			}

			$result = $this->processExprNode($expr->var, $closureCallScope ?? $scope, $nodeCallback, $context->enterDeep());
			$hasYield = $result->hasYield();
			$scope = $result->getScope();
			if (isset($closureCallScope)) {
				$scope = $scope->restoreOriginalScopeAfterClosureBind($originalScope);
			}
			$parametersAcceptor = null;
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $context->enterDeep())->getScope();
			} else {
				$calledOnType = $scope->getType($expr->var);
				$methodName = $expr->name->name;
				if ($calledOnType->hasMethod($methodName)->yes()) {
					$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
						$scope,
						$expr->args,
						$calledOnType->getMethod($methodName, $scope)->getVariants()
					);
				}
			}
			$result = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $context);
			$scope = $result->getScope();
			$hasYield = $hasYield || $result->hasYield();
		} elseif ($expr instanceof StaticCall) {
			if ($expr->class instanceof Expr) {
				$scope = $this->processExprNode($expr->class, $scope, $nodeCallback, $context->enterDeep())->getScope();
			}

			$parametersAcceptor = null;
			$hasYield = false;
			if ($expr->name instanceof Expr) {
				$result = $this->processExprNode($expr->name, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $result->hasYield();
				$scope = $result->getScope();
			} elseif ($expr->class instanceof Name) {
				$className = $scope->resolveName($expr->class);
				if ($this->broker->hasClass($className)) {
					$classReflection = $this->broker->getClass($className);
					if (is_string($expr->name)) {
						$methodName = $expr->name;
					} else {
						$methodName = $expr->name->name;
					}
					if ($classReflection->hasMethod($methodName)) {
						$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
							$scope,
							$expr->args,
							$classReflection->getMethod($methodName, $scope)->getVariants()
						);
						if (
							$classReflection->getName() === 'Closure'
							&& strtolower($methodName) === 'bind'
						) {
							$thisType = null;
							if (isset($expr->args[1])) {
								$argType = $scope->getType($expr->args[1]->value);
								if ($argType instanceof NullType) {
									$thisType = null;
								} else {
									$thisType = $argType;
								}
							}
							$scopeClass = 'static';
							if (isset($expr->args[2])) {
								$argValue = $expr->args[2]->value;
								$argValueType = $scope->getType($argValue);

								$directClassNames = TypeUtils::getDirectClassNames($argValueType);
								if (count($directClassNames) === 1) {
									$scopeClass = $directClassNames[0];
								} elseif (
									$argValue instanceof Expr\ClassConstFetch
									&& $argValue->name instanceof Node\Identifier
									&& strtolower($argValue->name->name) === 'class'
									&& $argValue->class instanceof Name
								) {
									$scopeClass = $scope->resolveName($argValue->class);
								} elseif ($argValueType instanceof ConstantStringType) {
									$scopeClass = $argValueType->getValue();
								}
							}
							$closureBindScope = $scope->enterClosureBind($thisType, $scopeClass);
						}
					}
				}
			}
			$result = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $context, $closureBindScope ?? null);
			$scope = $result->getScope();
			$hasYield = $hasYield || $result->hasYield();
		} elseif ($expr instanceof PropertyFetch) {
			$result = $this->processExprNode($expr->var, $scope, $nodeCallback, $context->enterDeep());
			$hasYield = $result->hasYield();
			$scope = $result->getScope();
			if ($expr->name instanceof Expr) {
				$result = $this->processExprNode($expr->name, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $hasYield || $result->hasYield();
				$scope = $result->getScope();
			}
		} elseif ($expr instanceof StaticPropertyFetch) {
			$hasYield = false;
			if ($expr->class instanceof Expr) {
				$result = $this->processExprNode($expr->class, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $result->hasYield();
				$scope = $result->getScope();
			}
			if ($expr->name instanceof Expr) {
				$result = $this->processExprNode($expr->name, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $hasYield || $result->hasYield();
				$scope = $result->getScope();
			}
		} elseif ($expr instanceof Expr\Closure) {
			return $this->processClosureNode($expr, $scope, $nodeCallback, $context, null);
		} elseif ($expr instanceof Expr\ClosureUse) {
			$this->processExprNode($expr->var, $scope, $nodeCallback, $context);
			$hasYield = false;
		} elseif ($expr instanceof Expr\ArrowFunction) {
			foreach ($expr->params as $param) {
				$this->processParamNode($param, $scope, $nodeCallback);
			}
			if ($expr->returnType !== null) {
				$nodeCallback($expr->returnType, $scope);
			}

			$arrowFunctionScope = $scope->enterArrowFunction($expr);
			$nodeCallback(new InArrowFunctionNode($expr), $arrowFunctionScope);
			$this->processExprNode($expr->expr, $arrowFunctionScope, $nodeCallback, $context);
			$hasYield = false;

		} elseif ($expr instanceof ErrorSuppress) {
			$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context);
			$hasYield = $result->hasYield();
			$scope = $result->getScope();
		} elseif ($expr instanceof Exit_) {
			$hasYield = false;
			if ($expr->expr !== null) {
				$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $result->hasYield();
				$scope = $result->getScope();
			}
		} elseif ($expr instanceof Node\Scalar\Encapsed) {
			$hasYield = false;
			foreach ($expr->parts as $part) {
				$result = $this->processExprNode($part, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $hasYield || $result->hasYield();
				$scope = $result->getScope();
			}
		} elseif ($expr instanceof ArrayDimFetch) {
			$hasYield = false;
			if ($expr->dim !== null) {
				$result = $this->processExprNode($expr->dim, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $result->hasYield();
				$scope = $result->getScope();
			}

			$result = $this->processExprNode($expr->var, $scope, $nodeCallback, $context->enterDeep());
			$hasYield = $hasYield || $result->hasYield();
			$scope = $result->getScope();
		} elseif ($expr instanceof Array_) {
			$itemNodes = [];
			$hasYield = false;
			foreach ($expr->items as $arrayItem) {
				$itemNodes[] = new LiteralArrayItem($scope, $arrayItem);
				$result = $this->processExprNode($arrayItem, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $hasYield || $result->hasYield();
				$scope = $result->getScope();
			}
			$nodeCallback(new LiteralArrayNode($expr, $itemNodes), $scope);
		} elseif ($expr instanceof ArrayItem) {
			$hasYield = false;
			if ($expr->key !== null) {
				$result = $this->processExprNode($expr->key, $scope, $nodeCallback, $context->enterDeep());
				$hasYield = $result->hasYield();
				$scope = $result->getScope();
			}
			$result = $this->processExprNode($expr->value, $scope, $nodeCallback, $context->enterDeep());
			$hasYield = $hasYield || $result->hasYield();
			$scope = $result->getScope();
		} elseif ($expr instanceof BooleanAnd || $expr instanceof BinaryOp\LogicalAnd) {
			$leftResult = $this->processExprNode($expr->left, $scope, $nodeCallback, $context->enterDeep());
			$rightResult = $this->processExprNode($expr->right, $leftResult->getTruthyScope(), $nodeCallback, $context);
			$leftMergedWithRightScope = $leftResult->getScope()->mergeWith($rightResult->getScope());

			return new ExpressionResult(
				$leftMergedWithRightScope,
				$leftResult->hasYield() || $rightResult->hasYield(),
				static function () use ($expr, $rightResult): Scope {
					return $rightResult->getScope()->filterByTruthyValue($expr);
				},
				static function () use ($leftMergedWithRightScope, $expr): Scope {
					return $leftMergedWithRightScope->filterByFalseyValue($expr);
				}
			);
			// todo do not execute right side if the left is false
		} elseif ($expr instanceof BooleanOr || $expr instanceof BinaryOp\LogicalOr) {
			$leftResult = $this->processExprNode($expr->left, $scope, $nodeCallback, $context->enterDeep());
			$rightResult = $this->processExprNode($expr->right, $leftResult->getFalseyScope(), $nodeCallback, $context);
			$leftMergedWithRightScope = $leftResult->getScope()->mergeWith($rightResult->getScope());

			return new ExpressionResult(
				$leftMergedWithRightScope,
				$leftResult->hasYield() || $rightResult->hasYield(),
				static function () use ($leftMergedWithRightScope, $expr): Scope {
					return $leftMergedWithRightScope->filterByTruthyValue($expr);
				},
				static function () use ($expr, $rightResult): Scope {
					return $rightResult->getScope()->filterByFalseyValue($expr);
				}
			);
			// todo do not execute right side if the left is true
		} elseif ($expr instanceof Coalesce) {
			$nonNullabilityResult = $this->ensureNonNullability($scope, $expr->left, false);

			if ($expr->left instanceof PropertyFetch) {
				$scope = $nonNullabilityResult->getScope();
			} else {
				$scope = $this->lookForEnterVariableAssign($nonNullabilityResult->getScope(), $expr->left);
			}
			$result = $this->processExprNode($expr->left, $scope, $nodeCallback, $context->enterDeep());
			$hasYield = $result->hasYield();
			$scope = $result->getScope();
			$scope = $this->revertNonNullability($scope, $nonNullabilityResult->getSpecifiedExpressions());

			if (!$expr->left instanceof PropertyFetch) {
				$scope = $this->lookForExitVariableAssign($scope, $expr->left);
			}
			$result = $this->processExprNode($expr->right, $scope, $nodeCallback, $context->enterDeep());
			$scope = $result->getScope()->mergeWith($scope);
			$hasYield = $hasYield || $result->hasYield();
		} elseif ($expr instanceof BinaryOp) {
			$result = $this->processExprNode($expr->left, $scope, $nodeCallback, $context->enterDeep());
			$scope = $result->getScope();
			$hasYield = $result->hasYield();
			$result = $this->processExprNode($expr->right, $scope, $nodeCallback, $context->enterDeep());
			$scope = $result->getScope();
			$hasYield = $hasYield || $result->hasYield();
		} elseif (
			$expr instanceof Expr\BitwiseNot
			|| $expr instanceof Cast
			|| $expr instanceof Expr\Clone_
			|| $expr instanceof Expr\Eval_
			|| $expr instanceof Expr\Include_
			|| $expr instanceof Expr\Print_
			|| $expr instanceof Expr\UnaryMinus
			|| $expr instanceof Expr\UnaryPlus
			|| $expr instanceof Expr\YieldFrom
		) {
			$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
			if ($expr instanceof Expr\YieldFrom) {
				$hasYield = true;
			} else {
				$hasYield = $result->hasYield();
			}

			$scope = $result->getScope();
		} elseif ($expr instanceof BooleanNot) {
			$scope = $scope->enterNegation();
			$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
			$scope = $result->getScope()->enterNegation();
			$hasYield = $result->hasYield();
		} elseif ($expr instanceof Expr\ClassConstFetch) {
			$hasYield = false;
			if ($expr->class instanceof Expr) {
				$result = $this->processExprNode($expr->class, $scope, $nodeCallback, $context->enterDeep());
				$scope = $result->getScope();
				$hasYield = $result->hasYield();
			}
		} elseif ($expr instanceof Expr\Empty_) {
			$nonNullabilityResult = $this->ensureNonNullability($scope, $expr->expr, true);
			$scope = $this->lookForEnterVariableAssign($nonNullabilityResult->getScope(), $expr->expr);
			$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
			$scope = $result->getScope();
			$hasYield = $result->hasYield();
			$scope = $this->revertNonNullability($scope, $nonNullabilityResult->getSpecifiedExpressions());
			$scope = $this->lookForExitVariableAssign($scope, $expr->expr);
		} elseif ($expr instanceof Expr\Isset_) {
			$hasYield = false;
			foreach ($expr->vars as $var) {
				$nonNullabilityResult = $this->ensureNonNullability($scope, $var, true);
				$scope = $this->lookForEnterVariableAssign($nonNullabilityResult->getScope(), $var);
				$result = $this->processExprNode($var, $scope, $nodeCallback, $context->enterDeep());
				$scope = $result->getScope();
				$hasYield = $hasYield || $result->hasYield();
				$scope = $this->revertNonNullability($scope, $nonNullabilityResult->getSpecifiedExpressions());
				$scope = $this->lookForExitVariableAssign($scope, $var);
			}
		} elseif ($expr instanceof Instanceof_) {
			$result = $this->processExprNode($expr->expr, $scope, $nodeCallback, $context->enterDeep());
			$scope = $result->getScope();
			$hasYield = $result->hasYield();
			if ($expr->class instanceof Expr) {
				$result = $this->processExprNode($expr->class, $scope, $nodeCallback, $context->enterDeep());
				$scope = $result->getScope();
				$hasYield = $hasYield || $result->hasYield();
			}
		} elseif ($expr instanceof List_) {
			// only in assign and foreach, processed elsewhere
			return new ExpressionResult($scope, false);
		} elseif ($expr instanceof New_) {
			$parametersAcceptor = null;
			$hasYield = false;
			if ($expr->class instanceof Expr) {
				$result = $this->processExprNode($expr->class, $scope, $nodeCallback, $context->enterDeep());
				$scope = $result->getScope();
				$hasYield = $result->hasYield();
			} elseif ($expr->class instanceof Class_) {
				$this->broker->getAnonymousClassReflection($expr->class, $scope); // populates $expr->class->name
				$this->processStmtNode($expr->class, $scope, $nodeCallback);
			} elseif ($this->broker->hasClass($expr->class->toString())) {
				$classReflection = $this->broker->getClass($expr->class->toString());
				if ($classReflection->hasConstructor()) {
					$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
						$scope,
						$expr->args,
						$classReflection->getConstructor()->getVariants()
					);
				}
			}
			$result = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $context);
			$scope = $result->getScope();
			$hasYield = $hasYield || $result->hasYield();
		} elseif (
			$expr instanceof Expr\PreInc
			|| $expr instanceof Expr\PostInc
			|| $expr instanceof Expr\PreDec
			|| $expr instanceof Expr\PostDec
		) {
			$result = $this->processExprNode($expr->var, $scope, $nodeCallback, $context->enterDeep());
			$scope = $result->getScope();
			$hasYield = $result->hasYield();
			if (
				$expr->var instanceof Variable
				|| $expr->var instanceof ArrayDimFetch
				|| $expr->var instanceof PropertyFetch
				|| $expr->var instanceof StaticPropertyFetch
			) {
				$expressionType = $scope->getType($expr);
				if (count(TypeUtils::getConstantScalars($expressionType)) > 0) {
					$newExpr = $expr;
					if ($expr instanceof Expr\PostInc) {
						$newExpr = new Expr\PreInc($expr->var);
					} elseif ($expr instanceof Expr\PostDec) {
						$newExpr = new Expr\PreDec($expr->var);
					}

					$scope = $this->processAssignVar(
						$scope,
						$expr->var,
						$newExpr,
						static function (): void {
						},
						$context,
						static function (Scope $scope): ExpressionResult {
							return new ExpressionResult($scope, false);
						},
						false
					)->getScope();
				}
			}
		} elseif ($expr instanceof Ternary) {
			$ternaryCondResult = $this->processExprNode($expr->cond, $scope, $nodeCallback, $context->enterDeep());
			$ifTrueScope = $ternaryCondResult->getTruthyScope();
			$ifFalseScope = $ternaryCondResult->getFalseyScope();

			if ($expr->if !== null) {
				$ifTrueScope = $this->processExprNode($expr->if, $ifTrueScope, $nodeCallback, $context)->getScope();
				$ifFalseScope = $this->processExprNode($expr->else, $ifFalseScope, $nodeCallback, $context)->getScope();
			} else {
				$ifFalseScope = $this->processExprNode($expr->else, $ifFalseScope, $nodeCallback, $context)->getScope();
			}

			$finalScope = $ifTrueScope->mergeWith($ifFalseScope);

			return new ExpressionResult(
				$finalScope,
				$ternaryCondResult->hasYield(),
				static function () use ($finalScope, $expr): Scope {
					return $finalScope->filterByTruthyValue($expr);
				},
				static function () use ($finalScope, $expr): Scope {
					return $finalScope->filterByFalseyValue($expr);
				}
			);

			// todo do not run else if cond is always true
			// todo do not run if branch if cond is always false
		} elseif ($expr instanceof Expr\Yield_) {
			if ($expr->key !== null) {
				$scope = $this->processExprNode($expr->key, $scope, $nodeCallback, $context->enterDeep())->getScope();
			}
			if ($expr->value !== null) {
				$scope = $this->processExprNode($expr->value, $scope, $nodeCallback, $context->enterDeep())->getScope();
			}
			$hasYield = true;
		} else {
			$hasYield = false;
		}

		return new ExpressionResult(
			$scope,
			$hasYield,
			static function () use ($scope, $expr): Scope {
				return $scope->filterByTruthyValue($expr);
			},
			static function () use ($scope, $expr): Scope {
				return $scope->filterByFalseyValue($expr);
			}
		);
	}

	/**
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param Expr $expr
	 * @param Scope $scope
	 * @param ExpressionContext $context
	 */
	private function callNodeCallbackWithExpression(
		\Closure $nodeCallback,
		Expr $expr,
		Scope $scope,
		ExpressionContext $context
	): void
	{
		if ($context->isDeep()) {
			$scope = $scope->exitFirstLevelStatements();
		}
		$nodeCallback($expr, $scope);
	}

	/**
	 * @param \PhpParser\Node\Expr\Closure $expr
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param ExpressionContext $context
	 * @param Type|null $passedToType
	 * @return \PHPStan\Analyser\ExpressionResult
	 */
	private function processClosureNode(
		Expr\Closure $expr,
		Scope $scope,
		\Closure $nodeCallback,
		ExpressionContext $context,
		?Type $passedToType
	): ExpressionResult
	{
		foreach ($expr->params as $param) {
			$this->processParamNode($param, $scope, $nodeCallback);
		}

		$byRefUses = [];

		if ($passedToType !== null && !$passedToType->isCallable()->no()) {
			$callableParameters = null;
			$acceptors = $passedToType->getCallableParametersAcceptors($scope);
			if (count($acceptors) === 1) {
				$callableParameters = $acceptors[0]->getParameters();
			}
		} else {
			$callableParameters = null;
		}

		$useScope = $scope;
		foreach ($expr->uses as $use) {
			if ($use->byRef) {
				$byRefUses[] = $use;
				$useScope = $useScope->enterExpressionAssign($use->var);

				$inAssignRightSideVariableName = $context->getInAssignRightSideVariableName();
				$inAssignRightSideType = $context->getInAssignRightSideType();
				if (
					$inAssignRightSideVariableName === $use->var->name
					&& $inAssignRightSideType !== null
				) {
					if ($inAssignRightSideType instanceof ClosureType) {
						$variableType = $inAssignRightSideType;
					} else {
						$alreadyHasVariableType = $scope->hasVariableType($inAssignRightSideVariableName);
						if ($alreadyHasVariableType->no()) {
							$variableType = TypeCombinator::union(new NullType(), $inAssignRightSideType);
						} else {
							$variableType = TypeCombinator::union($scope->getVariableType($inAssignRightSideVariableName), $inAssignRightSideType);
						}
					}
					$scope = $scope->assignVariable($inAssignRightSideVariableName, $variableType);
				}
			}
			$this->processExprNode($use, $useScope, $nodeCallback, $context);
			if (!$use->byRef) {
				continue;
			}

			$useScope = $useScope->exitExpressionAssign($use->var);
		}

		if ($expr->returnType !== null) {
			$nodeCallback($expr->returnType, $scope);
		}

		$closureScope = $scope->enterAnonymousFunction($expr, $callableParameters);
		$closureScope = $closureScope->processClosureScope($scope, null, $byRefUses);

		$gatheredReturnStatements = [];
		$closureStmtsCallback = static function (\PhpParser\Node $node, Scope $scope) use ($nodeCallback, &$gatheredReturnStatements): void {
			$nodeCallback($node, $scope);
			if (!$node instanceof Return_) {
				return;
			}

			$gatheredReturnStatements[] = new ReturnStatement($scope, $node);
		};
		if (count($byRefUses) === 0) {
			$statementResult = $this->processStmtNodes($expr, $expr->stmts, $closureScope, $closureStmtsCallback);
			$nodeCallback(new ClosureReturnStatementsNode(
				$expr,
				$gatheredReturnStatements,
				$statementResult
			), $closureScope);

			return new ExpressionResult($scope, false);
		}

		$count = 0;
		do {
			$prevScope = $closureScope;

			$intermediaryClosureScopeResult = $this->processStmtNodes($expr, $expr->stmts, $closureScope, static function (): void {
			});
			$intermediaryClosureScope = $intermediaryClosureScopeResult->getScope();
			foreach ($intermediaryClosureScopeResult->getExitPoints() as $exitPoint) {
				$intermediaryClosureScope = $intermediaryClosureScope->mergeWith($exitPoint->getScope());
			}
			$closureScope = $scope->enterAnonymousFunction($expr, $callableParameters);
			$closureScope = $closureScope->processClosureScope($intermediaryClosureScope, $prevScope, $byRefUses);
			if ($closureScope->equals($prevScope)) {
				break;
			}
			$count++;
		} while ($count < self::LOOP_SCOPE_ITERATIONS);

		$statementResult = $this->processStmtNodes($expr, $expr->stmts, $closureScope, $closureStmtsCallback);
		$nodeCallback(new ClosureReturnStatementsNode(
			$expr,
			$gatheredReturnStatements,
			$statementResult
		), $closureScope);

		return new ExpressionResult($scope->processClosureScope($closureScope, null, $byRefUses), false);
	}

	private function lookForArrayDestructuringArray(Scope $scope, Expr $expr, Type $valueType): Scope
	{
		if ($expr instanceof Array_ || $expr instanceof List_) {
			foreach ($expr->items as $key => $item) {
				/** @var \PhpParser\Node\Expr\ArrayItem|null $itemValue */
				$itemValue = $item;
				if ($itemValue === null) {
					continue;
				}

				$keyType = $itemValue->key === null ? new ConstantIntegerType($key) : $scope->getType($itemValue->key);
				$scope = $this->specifyItemFromArrayDestructuring($scope, $itemValue, $valueType, $keyType);
			}
		} elseif ($expr instanceof Variable && is_string($expr->name)) {
			$scope = $scope->assignVariable($expr->name, new MixedType());
		} elseif ($expr instanceof ArrayDimFetch && $expr->var instanceof Variable && is_string($expr->var->name)) {
			$scope = $scope->assignVariable($expr->var->name, new MixedType());
		}

		return $scope;
	}

	private function specifyItemFromArrayDestructuring(Scope $scope, ArrayItem $arrayItem, Type $valueType, Type $keyType): Scope
	{
		$type = $valueType->getOffsetValueType($keyType);

		$itemNode = $arrayItem->value;
		if ($itemNode instanceof Variable && is_string($itemNode->name)) {
			$scope = $scope->assignVariable($itemNode->name, $type);
		} elseif ($itemNode instanceof ArrayDimFetch && $itemNode->var instanceof Variable && is_string($itemNode->var->name)) {
			$currentType = $scope->hasVariableType($itemNode->var->name)->no()
				? new ConstantArrayType([], [])
				: $scope->getVariableType($itemNode->var->name);
			$dimType = null;
			if ($itemNode->dim !== null) {
				$dimType = $scope->getType($itemNode->dim);
			}
			$scope = $scope->assignVariable($itemNode->var->name, $currentType->setOffsetValueType($dimType, $type));
		} else {
			$scope = $this->lookForArrayDestructuringArray($scope, $itemNode, $type);
		}

		return $scope;
	}

	/**
	 * @param \PhpParser\Node\Param $param
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 */
	private function processParamNode(
		Node\Param $param,
		Scope $scope,
		\Closure $nodeCallback
	): void
	{
		if ($param->type !== null) {
			$nodeCallback($param->type, $scope);
		}
		if ($param->default === null) {
			return;
		}

		$this->processExprNode($param->default, $scope, $nodeCallback, ExpressionContext::createDeep());
	}

	/**
	 * @param ParametersAcceptor|null $parametersAcceptor
	 * @param \PhpParser\Node\Arg[] $args
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param ExpressionContext $context
	 * @param \PHPStan\Analyser\Scope|null $closureBindScope
	 * @return \PHPStan\Analyser\ExpressionResult
	 */
	private function processArgs(
		?ParametersAcceptor $parametersAcceptor,
		array $args,
		Scope $scope,
		\Closure $nodeCallback,
		ExpressionContext $context,
		?Scope $closureBindScope = null
	): ExpressionResult
	{
		// todo $scope = $scope->enterFunctionCall();
		if ($parametersAcceptor !== null) {
			$parameters = $parametersAcceptor->getParameters();
		}

		$hasYield = false;
		foreach ($args as $i => $arg) {
			$nodeCallback($arg, $scope);
			if (isset($parameters) && $parametersAcceptor !== null) {
				$assignByReference = false;
				if (isset($parameters[$i])) {
					$assignByReference = $parameters[$i]->passedByReference()->createsNewVariable();
					$parameterType = $parameters[$i]->getType();
				} elseif (count($parameters) > 0 && $parametersAcceptor->isVariadic()) {
					$lastParameter = $parameters[count($parameters) - 1];
					$assignByReference = $lastParameter->passedByReference()->createsNewVariable();
					$parameterType = $lastParameter->getType();
				}

				if ($assignByReference) {
					$argValue = $arg->value;
					if ($argValue instanceof Variable && is_string($argValue->name)) {
						$scope = $scope->assignVariable($argValue->name, new MixedType());
					}
				}
			}

			$originalScope = $scope;
			$scopeToPass = $scope;
			if ($i === 0 && $closureBindScope !== null) {
				$scopeToPass = $closureBindScope;
			}

			if ($arg->value instanceof Expr\Closure) {
				$this->callNodeCallbackWithExpression($nodeCallback, $arg->value, $scopeToPass, $context);
				$result = $this->processClosureNode($arg->value, $scopeToPass, $nodeCallback, $context, $parameterType ?? null);
			} else {
				$result = $this->processExprNode($arg->value, $scopeToPass, $nodeCallback, $context->enterDeep());
			}
			$scope = $result->getScope();
			$hasYield = $hasYield || $result->hasYield();
			if ($i !== 0 || $closureBindScope === null) {
				continue;
			}

			$scope = $scope->restoreOriginalScopeAfterClosureBind($originalScope);
		}

		// todo $scope = $scope->exitFunctionCall();

		return new ExpressionResult($scope, $hasYield);
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Expr $var
	 * @param \PhpParser\Node\Expr $assignedExpr
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param ExpressionContext $context
	 * @param \Closure(Scope $scope): ExpressionResult $processExprCallback
	 * @param bool $enterExpressionAssign
	 * @return ExpressionResult
	 */
	private function processAssignVar(
		Scope $scope,
		Expr $var,
		Expr $assignedExpr,
		\Closure $nodeCallback,
		ExpressionContext $context,
		\Closure $processExprCallback,
		bool $enterExpressionAssign
	): ExpressionResult
	{
		$nodeCallback($var, $enterExpressionAssign ? $scope->enterExpressionAssign($var) : $scope);
		$hasYield = false;
		if ($var instanceof Variable && is_string($var->name)) {
			$result = $processExprCallback($scope);
			$hasYield = $result->hasYield();
			$scope = $result->getScope();
			$scope = $scope->assignVariable($var->name, $scope->getType($assignedExpr));
		} elseif ($var instanceof ArrayDimFetch) {
			$dimExprStack = [];
			while ($var instanceof ArrayDimFetch) {
				$dimExprStack[] = $var->dim;
				$var = $var->var;
			}

			// 1. eval root expr
			if ($enterExpressionAssign && $var instanceof Variable) {
				$scope = $scope->enterExpressionAssign($var);
			}
			$result = $this->processExprNode($var, $scope, $nodeCallback, $context->enterDeep());
			$hasYield = $result->hasYield();
			$scope = $result->getScope();
			if ($enterExpressionAssign && $var instanceof Variable) {
				$scope = $scope->exitExpressionAssign($var);
			}

			// 2. eval dimensions
			$offsetTypes = [];
			foreach (array_reverse($dimExprStack) as $dimExpr) {
				if ($dimExpr === null) {
					$offsetTypes[] = null;

				} else {
					$offsetTypes[] = $scope->getType($dimExpr);

					if ($enterExpressionAssign) {
						$scope->enterExpressionAssign($dimExpr);
					}
					$result = $this->processExprNode($dimExpr, $scope, $nodeCallback, $context->enterDeep());
					$hasYield = $hasYield || $result->hasYield();
					$scope = $result->getScope();

					if ($enterExpressionAssign) {
						$scope = $scope->exitExpressionAssign($dimExpr);
					}
				}
			}

			$valueToWrite = $scope->getType($assignedExpr);

			// 3. eval assigned expr
			$result = $processExprCallback($scope);
			$hasYield = $hasYield || $result->hasYield();
			$scope = $result->getScope();

			// 4. compose types
			$varType = $scope->getType($var);
			if ($varType instanceof ErrorType) {
				$varType = new ConstantArrayType([], []);
			}
			$offsetValueType = $varType;
			$offsetValueTypeStack = [$offsetValueType];
			foreach (array_slice($offsetTypes, 0, -1) as $offsetType) {
				if ($offsetType === null) {
					$offsetValueType = new ConstantArrayType([], []);

				} else {
					$offsetValueType = $offsetValueType->getOffsetValueType($offsetType);
					if ($offsetValueType instanceof ErrorType) {
						$offsetValueType = new ConstantArrayType([], []);
					}
				}

				$offsetValueTypeStack[] = $offsetValueType;
			}

			foreach (array_reverse($offsetTypes) as $offsetType) {
				/** @var Type $offsetValueType */
				$offsetValueType = array_pop($offsetValueTypeStack);
				$valueToWrite = $offsetValueType->setOffsetValueType($offsetType, $valueToWrite);
			}

			if ($var instanceof Variable && is_string($var->name)) {
				$scope = $scope->assignVariable($var->name, $valueToWrite);
			} else {
				$scope = $scope->specifyExpressionType(
					$var,
					$valueToWrite
				);
			}
		} elseif ($var instanceof PropertyFetch) {
			$result = $processExprCallback($scope);
			$hasYield = $result->hasYield();
			$scope = $result->getScope();

			$propertyHolderType = $scope->getType($var->var);
			$propertyName = null;
			if ($var->name instanceof Node\Identifier) {
				$propertyName = $var->name->name;
			}
			if ($propertyName !== null && $propertyHolderType->hasProperty($propertyName)->yes()) {
				$propertyReflection = $propertyHolderType->getProperty($propertyName, $scope);
				if (!$propertyReflection instanceof ExtendedPropertyReflection || $propertyReflection->canChangeTypeAfterAssignment()) {
					$scope = $scope->specifyExpressionType($var, $scope->getType($assignedExpr));
				}
			} else {
				// fallback
				$scope = $scope->specifyExpressionType($var, $scope->getType($assignedExpr));
			}

		} elseif ($var instanceof Expr\StaticPropertyFetch) {
			$result = $processExprCallback($scope);
			$hasYield = $result->hasYield();
			$scope = $result->getScope();

			if ($var->class instanceof \PhpParser\Node\Name) {
				$propertyHolderType = new ObjectType($scope->resolveName($var->class));
			} else {
				$propertyHolderType = $scope->getType($var->class);
			}
			$propertyName = null;
			if ($var->name instanceof Node\Identifier) {
				$propertyName = $var->name->name;
			}
			if ($propertyName !== null && $propertyHolderType->hasProperty($propertyName)->yes()) {
				$propertyReflection = $propertyHolderType->getProperty($propertyName, $scope);
				if (!$propertyReflection instanceof ExtendedPropertyReflection || $propertyReflection->canChangeTypeAfterAssignment()) {
					$scope = $scope->specifyExpressionType($var, $scope->getType($assignedExpr));
				}
			} else {
				// fallback
				$scope = $scope->specifyExpressionType($var, $scope->getType($assignedExpr));
			}
		}

		return new ExpressionResult($scope, $hasYield);
	}

	private function processStmtVarAnnotation(Scope $scope, Node\Stmt $stmt): Scope
	{
		if (!$this->allowVarTagAboveStatements) {
			return $scope;
		}
		$comment = CommentHelper::getDocComment($stmt);
		if ($comment === null) {
			return $scope;
		}

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$scope->isInClass() ? $scope->getClassReflection()->getName() : null,
			$scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
			$comment
		);
		foreach ($resolvedPhpDoc->getVarTags() as $name => $varTag) {
			if (is_int($name)) {
				continue;
			}

			$certainty = $scope->hasVariableType($name);
			if ($certainty->no()) {
				continue;
			}

			$scope = $scope->assignVariable($name, $varTag->getType(), $certainty);
		}

		return $scope;
	}

	private function processVarAnnotation(Scope $scope, string $variableName, string $comment, bool $strict, bool &$changed = false): Scope
	{
		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$scope->isInClass() ? $scope->getClassReflection()->getName() : null,
			$scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
			$comment
		);
		$varTags = $resolvedPhpDoc->getVarTags();

		if (isset($varTags[$variableName])) {
			$variableType = $varTags[$variableName]->getType();
			$changed = true;
			return $scope->assignVariable($variableName, $variableType);

		}

		if (!$strict && count($varTags) === 1 && isset($varTags[0])) {
			$variableType = $varTags[0]->getType();
			$changed = true;
			return $scope->assignVariable($variableName, $variableType);

		}

		return $scope;
	}

	private function enterForeach(Scope $scope, Foreach_ $stmt): Scope
	{
		if ($stmt->keyVar !== null && $stmt->keyVar instanceof Variable && is_string($stmt->keyVar->name)) {
			$scope = $scope->assignVariable($stmt->keyVar->name, new MixedType());
		}

		$comment = CommentHelper::getDocComment($stmt);
		if ($stmt->valueVar instanceof Variable && is_string($stmt->valueVar->name)) {
			$scope = $scope->enterForeach(
				$stmt->expr,
				$stmt->valueVar->name,
				$stmt->keyVar !== null
				&& $stmt->keyVar instanceof Variable
				&& is_string($stmt->keyVar->name)
					? $stmt->keyVar->name
					: null
			);
			if ($comment !== null) {
				$scope = $this->processVarAnnotation($scope, $stmt->valueVar->name, $comment, true);
			}
		}

		if (
			$stmt->keyVar instanceof Variable && is_string($stmt->keyVar->name)
			&& $comment !== null
		) {
			$scope = $this->processVarAnnotation($scope, $stmt->keyVar->name, $comment, true);
		}

		if ($stmt->valueVar instanceof List_ || $stmt->valueVar instanceof Array_) {
			$exprType = $scope->getType($stmt->expr);
			$itemType = $exprType->getIterableValueType();
			$scope = $this->lookForArrayDestructuringArray($scope, $stmt->valueVar, $itemType);
			$comment = CommentHelper::getDocComment($stmt);
			if ($comment !== null) {
				foreach ($stmt->valueVar->items as $arrayItem) {
					if ($arrayItem === null) {
						continue;
					}
					if (!$arrayItem->value instanceof Variable || !is_string($arrayItem->value->name)) {
						continue;
					}

					$scope = $this->processVarAnnotation($scope, $arrayItem->value->name, $comment, true);
				}
			}
		}

		return $scope;
	}

	private function processTraitUse(Node\Stmt\TraitUse $node, Scope $classScope, \Closure $nodeCallback): void
	{
		foreach ($node->traits as $trait) {
			$traitName = (string) $trait;
			if (!$this->broker->hasClass($traitName)) {
				continue;
			}
			$traitReflection = $this->broker->getClass($traitName);
			$traitFileName = $traitReflection->getFileName();
			if ($traitFileName === false) {
				continue; // trait from eval or from PHP itself
			}
			$fileName = $this->fileHelper->normalizePath($traitFileName);
			if (!isset($this->analysedFiles[$fileName])) {
				continue;
			}
			$parserNodes = $this->parser->parseFile($fileName);
			$this->processNodesForTraitUse($parserNodes, $traitReflection, $classScope, $nodeCallback);
		}
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node|scalar $node
	 * @param ClassReflection $traitReflection
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node): void $nodeCallback
	 */
	private function processNodesForTraitUse($node, ClassReflection $traitReflection, Scope $scope, \Closure $nodeCallback): void
	{
		if ($node instanceof Node) {
			if ($node instanceof Node\Stmt\Trait_ && $traitReflection->getName() === (string) $node->namespacedName) {
				$this->processStmtNodes($node, $node->stmts, $scope->enterTrait($traitReflection), $nodeCallback);
				return;
			}
			if ($node instanceof Node\Stmt\ClassLike) {
				return;
			}
			if ($node instanceof Node\FunctionLike) {
				return;
			}
			foreach ($node->getSubNodeNames() as $subNodeName) {
				$subNode = $node->{$subNodeName};
				$this->processNodesForTraitUse($subNode, $traitReflection, $scope, $nodeCallback);
			}
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$this->processNodesForTraitUse($subNode, $traitReflection, $scope, $nodeCallback);
			}
		}
	}

	/**
	 * @param Scope $scope
	 * @param Node\FunctionLike $functionLike
	 * @return array{Type[], ?Type, ?Type, ?string, bool, bool, bool}
	 */
	public function getPhpDocs(Scope $scope, Node\FunctionLike $functionLike): array
	{
		$phpDocParameterTypes = [];
		$phpDocReturnType = null;
		$phpDocThrowType = null;
		$deprecatedDescription = null;
		$isDeprecated = false;
		$isInternal = false;
		$isFinal = false;
		$docComment = $functionLike->getDocComment() !== null
			? $functionLike->getDocComment()->getText()
			: null;

		$file = $scope->getFile();
		$class = $scope->isInClass() ? $scope->getClassReflection()->getName() : null;
		$trait = $scope->isInTrait() ? $scope->getTraitReflection()->getName() : null;
		$isExplicitPhpDoc = true;
		if ($functionLike instanceof Node\Stmt\ClassMethod) {
			if (!$scope->isInClass()) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
				$this->broker,
				$docComment,
				$scope->getClassReflection()->getName(),
				$trait,
				$functionLike->name->name,
				$file
			);

			if ($phpDocBlock !== null) {
				$docComment = $phpDocBlock->getDocComment();
				$file = $phpDocBlock->getFile();
				$class = $phpDocBlock->getClass();
				$trait = $phpDocBlock->getTrait();
				$isExplicitPhpDoc = $phpDocBlock->isExplicit();
			}
		}

		if ($docComment !== null) {
			$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
				$file,
				$class,
				$trait,
				$docComment
			);
			$phpDocParameterTypes = array_map(static function (ParamTag $tag): Type {
				return $tag->getType();
			}, $resolvedPhpDoc->getParamTags());
			$nativeReturnType = $scope->getFunctionType($functionLike->getReturnType(), false, false);
			$phpDocReturnType = null;
			if (
				$resolvedPhpDoc->getReturnTag() !== null
				&& (
					$isExplicitPhpDoc
					|| $nativeReturnType->isSuperTypeOf($resolvedPhpDoc->getReturnTag()->getType())->yes()
				)
			) {
				$phpDocReturnType = $resolvedPhpDoc->getReturnTag()->getType();
			}
			$phpDocThrowType = $resolvedPhpDoc->getThrowsTag() !== null ? $resolvedPhpDoc->getThrowsTag()->getType() : null;
			$deprecatedDescription = $resolvedPhpDoc->getDeprecatedTag() !== null ? $resolvedPhpDoc->getDeprecatedTag()->getMessage() : null;
			$isDeprecated = $resolvedPhpDoc->isDeprecated();
			$isInternal = $resolvedPhpDoc->isInternal();
			$isFinal = $resolvedPhpDoc->isFinal();
		}

		return [$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $deprecatedDescription, $isDeprecated, $isInternal, $isFinal];
	}

}
