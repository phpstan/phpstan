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
use PHPStan\Node\InClassMethodNode;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
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
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
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
		array $earlyTerminatingMethodCalls
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
		foreach ($nodes as $node) {
			if (!$node instanceof Node\Stmt) {
				continue;
			}

			$statementResult = $this->processStmtNode($node, $scope, $nodeCallback);
			/*if ($statementResult->isAlwaysTerminating()) {
				// todo virtual dead code node
				// todo test various pollute* settings
				//break;
			}*/

			$scope = $statementResult->getScope();
		}
	}

	/**
	 * @param \PhpParser\Node\Stmt[] $stmts
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @return StatementResult
	 */
	public function processStmtNodes(
		array $stmts,
		Scope $scope,
		\Closure $nodeCallback
	): StatementResult
	{
		$exitPoints = [];
		$alwaysTerminatingStatements = [];
		$alreadyTerminated = false;
		foreach ($stmts as $stmt) {
			$statementResult = $this->processStmtNode($stmt, $scope, $nodeCallback);
			$exitPoints = array_merge($exitPoints, $statementResult->getExitPoints());

			if ($statementResult->isAlwaysTerminating() && !$alreadyTerminated) {
				$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $statementResult->getAlwaysTerminatingStatements());
				$alreadyTerminated = true;
				// todo break;
			}

			$scope = $statementResult->getScope();
		}

		return new StatementResult($scope, $alwaysTerminatingStatements, $exitPoints);
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

		// todo handle all stmt descendants
		if ($stmt instanceof Node\Stmt\Declare_) {
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
			[$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $isDeprecated, $isInternal, $isFinal] = $this->getPhpDocs($scope, $stmt);

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
				$isDeprecated,
				$isInternal,
				$isFinal
			);
			$this->processStmtNodes($stmt->stmts, $functionScope, $nodeCallback);
		} elseif ($stmt instanceof Node\Stmt\ClassMethod) {
			[$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $isDeprecated, $isInternal, $isFinal] = $this->getPhpDocs($scope, $stmt);

			foreach ($stmt->params as $param) {
				$this->processParamNode($param, $scope, $nodeCallback);
			}

			if ($stmt->returnType !== null) {
				$nodeCallback($stmt->returnType, $scope);
			}

			$methodScope = $scope->enterClassMethod(
				$stmt,
				$phpDocParameterTypes,
				$phpDocReturnType,
				$phpDocThrowType,
				$isDeprecated,
				$isInternal,
				$isFinal
			);
			$nodeCallback(new InClassMethodNode($stmt), $methodScope);

			if ($stmt->stmts !== null) {
				$this->processStmtNodes($stmt->stmts, $methodScope, $nodeCallback);
			}
		} elseif ($stmt instanceof Echo_) {
			foreach ($stmt->exprs as $echoExpr) {
				$scope = $this->processExprNode($echoExpr, $scope, $nodeCallback, 1);
			}
		} elseif ($stmt instanceof Return_) {
			if ($stmt->expr !== null) {
				$scope = $this->processExprNode($stmt->expr, $scope, $nodeCallback, 1);
			}

			return new StatementResult($scope, [$stmt], [
				new StatementExitPoint($stmt, $scope),
			]);
		} elseif ($stmt instanceof Continue_) {
			if ($stmt->num !== null) {
				$scope = $this->processExprNode($stmt->num, $scope, $nodeCallback, 1);
			}

			return new StatementResult($scope, [$stmt], [
				new StatementExitPoint($stmt, $scope),
			]);
		} elseif ($stmt instanceof Break_) {
			if ($stmt->num !== null) {
				$scope = $this->processExprNode($stmt->num, $scope, $nodeCallback, 1);
			}

			return new StatementResult($scope, [$stmt], [
				new StatementExitPoint($stmt, $scope),
			]);
		} elseif ($stmt instanceof Node\Stmt\Expression) {
			$earlyTerminationExpr = $this->findEarlyTerminatingExpr($stmt->expr, $scope);
			$scope = $this->processExprNode($stmt->expr, $scope, $nodeCallback, 0);
			$scope = $scope->filterBySpecifiedTypes($this->typeSpecifier->specifyTypesInCondition(
				$scope,
				$stmt->expr,
				TypeSpecifierContext::createNull()
			));
			if ($earlyTerminationExpr !== null) {
				return new StatementResult($scope, [$stmt], [
					new StatementExitPoint($stmt, $scope),
				]);
			}
		} elseif ($stmt instanceof Node\Stmt\Namespace_) {
			if ($stmt->name !== null) {
				$scope = $scope->enterNamespace($stmt->name->toString());
			}

			$scope = $this->processStmtNodes($stmt->stmts, $scope, $nodeCallback)->getScope();
		} elseif ($stmt instanceof Node\Stmt\Trait_) {
			return new StatementResult($scope, [], []);
		} elseif ($stmt instanceof Node\Stmt\ClassLike) {
			if (isset($stmt->namespacedName)) {
				$classScope = $scope->enterClass($this->broker->getClass((string) $stmt->namespacedName));
			} elseif ($stmt instanceof Class_) {
				$classScope = $scope->enterAnonymousClass($this->broker->getAnonymousClassReflection($stmt, $scope));
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$this->processStmtNodes($stmt->stmts, $classScope, $nodeCallback);
		} elseif ($stmt instanceof Node\Stmt\Property) {
			foreach ($stmt->props as $prop) {
				$this->processStmtNode($prop, $scope, $nodeCallback);
			}

			if ($stmt->type !== null) {
				$nodeCallback($stmt->type, $scope);
			}
		} elseif ($stmt instanceof Node\Stmt\PropertyProperty) {
			if ($stmt->default !== null) {
				$this->processExprNode($stmt->default, $scope, $nodeCallback, 1);
			}
		} elseif ($stmt instanceof Throw_) {
			$scope = $this->processExprNode($stmt->expr, $scope, $nodeCallback, 1);
			return new StatementResult($scope, [$stmt], [
				new StatementExitPoint($stmt, $scope),
			]);
		} elseif ($stmt instanceof If_) {
			$conditionType = $scope->getType($stmt->cond)->toBoolean();
			$ifAlwaysTrue = $conditionType instanceof ConstantBooleanType && $conditionType->getValue();
			$scope = $this->processExprNode($stmt->cond, $scope, $nodeCallback, 1);
			$exitPoints = [];
			$finalScope = null;
			$alwaysTerminatingStatements = [];
			$alwaysTerminating = true;

			$branchScopeStatementResult = $this->processStmtNodes($stmt->stmts, $scope->filterByTruthyValue($stmt->cond), $nodeCallback);

			if (!$conditionType instanceof ConstantBooleanType || $conditionType->getValue()) {
				$exitPoints = $branchScopeStatementResult->getExitPoints();
				$branchScope = $branchScopeStatementResult->getScope();
				$finalScope = $branchScopeStatementResult->isAlwaysTerminating() ? null : $branchScope;
				$alwaysTerminating = $branchScopeStatementResult->isAlwaysTerminating();
				if ($branchScopeStatementResult->isAlwaysTerminating()) {
					$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $branchScopeStatementResult->getAlwaysTerminatingStatements());
				}
			}

			$scope = $scope->filterByFalseyValue($stmt->cond);
			$lastElseIfConditionIsTrue = false;

			$condScope = $scope;
			foreach ($stmt->elseifs as $elseif) {
				$nodeCallback($elseif, $scope);
				$elseIfConditionType = $condScope->getType($elseif->cond)->toBoolean();
				$condScope = $this->processExprNode($elseif->cond, $condScope, $nodeCallback, 1);
				$branchScopeStatementResult = $this->processStmtNodes($elseif->stmts, $condScope->filterByTruthyValue($elseif->cond), $nodeCallback);

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
					if ($branchScopeStatementResult->isAlwaysTerminating()) {
						$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $branchScopeStatementResult->getAlwaysTerminatingStatements());
					}
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
				$branchScopeStatementResult = $this->processStmtNodes($stmt->else->stmts, $scope, $nodeCallback);

				if (!$ifAlwaysTrue && !$lastElseIfConditionIsTrue) {
					$exitPoints = array_merge($exitPoints, $branchScopeStatementResult->getExitPoints());
					$branchScope = $branchScopeStatementResult->getScope();
					$finalScope = $branchScopeStatementResult->isAlwaysTerminating() ? $finalScope : $branchScope->mergeWith($finalScope);
					$alwaysTerminating = $alwaysTerminating && $branchScopeStatementResult->isAlwaysTerminating();
					if ($branchScopeStatementResult->isAlwaysTerminating()) {
						$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $branchScopeStatementResult->getAlwaysTerminatingStatements());
					}
				}
			}

			if ($finalScope === null) {
				$finalScope = $scope;
			}

			return new StatementResult($finalScope, $alwaysTerminating ? $alwaysTerminatingStatements : [], $exitPoints);
		} elseif ($stmt instanceof Node\Stmt\TraitUse) {
			$this->processTraitUse($stmt, $scope, $nodeCallback);
		} elseif ($stmt instanceof Foreach_) {
			$scope = $this->processExprNode($stmt->expr, $scope, $nodeCallback, 1);
			$bodyScope = $this->enterForeach($scope, $stmt);
			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScope = $bodyScope->mergeWith($scope);
				$bodyScope = $this->enterForeach($bodyScope, $stmt);
				$bodyScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopTerminationStatements();
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
			$finalScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, $nodeCallback)->filterOutLoopTerminationStatements();
			$alwaysTerminatingStatements = $finalScopeResult->getAlwaysTerminatingStatements();
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

			return new StatementResult($finalScope, $alwaysTerminating ? $alwaysTerminatingStatements : [], []);
		} elseif ($stmt instanceof While_) {
			$condScope = $this->processExprNode($stmt->cond, $scope, static function (): void {
			}, 1);
			//TODO if (!$condScope->equals($scope)) {
			//	$condScope = $condScope->generalizeWith($scope);
			//}
			$bodyScope = $condScope->filterByTruthyValue($stmt->cond);
			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScope = $bodyScope->mergeWith($scope);
				$bodyScope = $this->processExprNode($stmt->cond, $bodyScope, static function (): void {
				}, 1)->filterByTruthyValue($stmt->cond);
				$bodyScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopTerminationStatements();
				$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
				$alwaysTerminatingStatements = $bodyScopeResult->getAlwaysTerminatingStatements();
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
			$bodyScope = $this->processExprNode($stmt->cond, $bodyScope, $nodeCallback, 1)->filterByTruthyValue($stmt->cond);
			$finalScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, $nodeCallback);
			$finalScope = $finalScopeResult->getScope();
			foreach ($finalScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
				$finalScope = $finalScope->mergeWith($continueExitPoint->getScope());
			}
			foreach ($finalScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
				$finalScope = $finalScope->mergeWith($breakExitPoint->getScope());
			}

			$condBooleanType = $scope->getType($stmt->cond)->toBoolean();
			$isIterableAtLeastOnce = $condBooleanType instanceof ConstantBooleanType && $condBooleanType->getValue();
			// todo for all loops - is not falsey when the loop is exited via break
			$condScope = $condScope->filterByFalseyValue($stmt->cond);
			if (!$isIterableAtLeastOnce) {
				if (!$this->polluteScopeWithLoopInitialAssignments) {
					$condScope = $condScope->mergeWith($scope);
				}
				$finalScope = $finalScope->mergeWith($condScope);
			}

			return new StatementResult($finalScope, $alwaysTerminatingStatements, []);
		} elseif ($stmt instanceof Do_) {
			$finalScope = null;
			$bodyScope = $scope;
			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopTerminationStatements();
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
				}, 1)->filterByTruthyValue($stmt->cond);
				if ($bodyScope->equals($prevScope)) {
					break;
				}

				if ($count >= self::GENERALIZE_AFTER_ITERATION) {
					$bodyScope = $bodyScope->generalizeWith($prevScope);
				}
				$count++;
			} while (!$alwaysTerminating && $count < self::LOOP_SCOPE_ITERATIONS);

			$bodyScope = $bodyScope->mergeWith($scope);

			$bodyScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, $nodeCallback)->filterOutLoopTerminationStatements();
			$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
			$alwaysTerminatingStatements = $bodyScopeResult->getAlwaysTerminatingStatements();
			$bodyScope = $bodyScopeResult->getScope();
			foreach ($bodyScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
				$bodyScope = $bodyScope->mergeWith($continueExitPoint->getScope());
			}
			$finalScope = $alwaysTerminating ? $finalScope : $bodyScope->mergeWith($finalScope);
			if ($finalScope === null) {
				$finalScope = $scope;
			}
			if (!$alwaysTerminating) {
				$finalScope = $this->processExprNode($stmt->cond, $bodyScope, $nodeCallback, 1);
				// todo not if it breaks out of the loop using break;
				$finalScope = $finalScope->filterByFalseyValue($stmt->cond);
			}
			foreach ($bodyScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
				$finalScope = $breakExitPoint->getScope()->mergeWith($finalScope);
			}

			return new StatementResult($finalScope, $alwaysTerminatingStatements, []);
		} elseif ($stmt instanceof For_) {
			$initScope = $scope;
			foreach ($stmt->init as $initExpr) {
				$initScope = $this->processExprNode($initExpr, $initScope, $nodeCallback, 0);
			}

			$bodyScope = $initScope;
			foreach ($stmt->cond as $condExpr) {
				$bodyScope = $this->processExprNode($condExpr, $bodyScope, static function (): void {
				}, 1)->filterByTruthyValue($condExpr);
			}

			$count = 0;
			do {
				$prevScope = $bodyScope;
				$bodyScope = $bodyScope->mergeWith($initScope);
				foreach ($stmt->cond as $condExpr) {
					$bodyScope = $this->processExprNode($condExpr, $bodyScope, static function (): void {
					}, 1)->filterByTruthyValue($condExpr);
				}
				$bodyScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, static function (): void {
				})->filterOutLoopTerminationStatements();
				$alwaysTerminating = $bodyScopeResult->isAlwaysTerminating();
				$alwaysTerminatingStatements = $bodyScopeResult->getAlwaysTerminatingStatements();
				$bodyScope = $bodyScopeResult->getScope();
				foreach ($bodyScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
					$bodyScope = $bodyScope->mergeWith($continueExitPoint->getScope());
				}
				foreach ($stmt->loop as $loopExpr) {
					$bodyScope = $this->processExprNode($loopExpr, $bodyScope, static function (): void {
					}, 0);
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
				$bodyScope = $this->processExprNode($condExpr, $bodyScope, $nodeCallback, 1)->filterByTruthyValue($condExpr);
			}

			$finalScopeResult = $this->processStmtNodes($stmt->stmts, $bodyScope, $nodeCallback);
			$finalScope = $finalScopeResult->getScope();
			foreach ($finalScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
				$finalScope = $continueExitPoint->getScope()->mergeWith($finalScope);
			}
			foreach ($stmt->loop as $loopExpr) {
				$finalScope = $this->processExprNode($loopExpr, $finalScope, $nodeCallback, 0);
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

			return new StatementResult($finalScope, $alwaysTerminatingStatements, []);
		} elseif ($stmt instanceof Switch_) {
			$scope = $this->processExprNode($stmt->cond, $scope, $nodeCallback, 1);
			$scopeForBranches = $scope;
			$finalScope = null;
			$prevScope = null;
			$hasDefaultCase = false;
			$alwaysTerminating = true;
			$alwaysTerminatingStatements = [];
			foreach ($stmt->cases as $i => $caseNode) {
				if ($caseNode->cond !== null) {
					$condExpr = new BinaryOp\Equal($stmt->cond, $caseNode->cond);
					$scopeForBranches = $this->processExprNode($caseNode->cond, $scopeForBranches, $nodeCallback, 1);
					if ($prevScope === null) {
						$branchScope = $scopeForBranches->filterByTruthyValue($condExpr);
						$scopeForBranches = $scopeForBranches->filterByFalseyValue($condExpr);
					} else {
						$branchScope = $scopeForBranches;
					}
				} else {
					$hasDefaultCase = true;
					$branchScope = $scopeForBranches;
				}

				$branchScope = $branchScope->mergeWith($prevScope);
				$branchScopeResult = $this->processStmtNodes($caseNode->stmts, $branchScope, $nodeCallback);
				$branchScope = $branchScopeResult->getScope();
				$branchFinalScopeResult = $branchScopeResult->filterOutLoopTerminationStatements();
				$alwaysTerminating = $alwaysTerminating && $branchFinalScopeResult->isAlwaysTerminating();
				if ($branchFinalScopeResult->isAlwaysTerminating()) {
					$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $branchFinalScopeResult->getAlwaysTerminatingStatements());
				}
				$isLastCase = ($i === count($stmt->cases) - 1);
				if (
					$branchScopeResult->areAllAlwaysTerminatingStatementsLoopTerminationStatements()
					|| (
						$isLastCase
						&& !$branchFinalScopeResult->isAlwaysTerminating()
					)
				) {
					$finalScope = $branchScope->mergeWith($finalScope);
					foreach ($branchScopeResult->getExitPointsByType(Break_::class) as $breakExitPoint) {
						$finalScope = $finalScope->mergeWith($breakExitPoint->getScope());
					}
					foreach ($branchScopeResult->getExitPointsByType(Continue_::class) as $continueExitPoint) {
						$finalScope = $finalScope->mergeWith($continueExitPoint->getScope());
					}
				}

				$prevScope = $branchScopeResult->isAlwaysTerminating() ? null : $branchScope;
			}

			if (!$hasDefaultCase) {
				$alwaysTerminating = false;
			}

			if (!$hasDefaultCase || $finalScope === null) {
				$finalScope = $scope->mergeWith($finalScope);
			}

			// todo
			// todo StatementResultTest
			return new StatementResult($finalScope, $alwaysTerminating ? $alwaysTerminatingStatements : [], []);
		} elseif ($stmt instanceof TryCatch) {
			// todo polluteCatchScopeWithTryAssignments
			$branchScopeResult = $this->processStmtNodes($stmt->stmts, $scope, $nodeCallback);
			$branchScope = $branchScopeResult->getScope();
			$tryScope = $branchScope;
			$exitPoints = [];
			$alwaysTerminatingStatements = [];
			$finalScope = $branchScopeResult->isAlwaysTerminating() ? null : $branchScope;
			$alwaysTerminating = $branchScopeResult->isAlwaysTerminating();
			if ($branchScopeResult->isAlwaysTerminating()) {
				$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $branchScopeResult->getAlwaysTerminatingStatements());
			}
			// todo process finallyScope only if the block exists
			$finallyScope = $branchScope;
			foreach ($branchScopeResult->getExitPoints() as $exitPoint) {
				$finallyScope = $finallyScope->mergeWith($exitPoint->getScope());
				$exitPoints[] = $exitPoint;
			}

			foreach ($stmt->catches as $catchNode) {
				$nodeCallback($catchNode, $scope);

				if ($this->polluteCatchScopeWithTryAssignments) {
					$catchScope = $tryScope;
				} else {
					$catchScope = $scope->mergeWith($tryScope);
				}

				if (!is_string($catchNode->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				$catchScope = $catchScope->enterCatch($catchNode->types, $catchNode->var->name);
				$catchScopeResult = $this->processStmtNodes($catchNode->stmts, $catchScope, $nodeCallback);
				$catchScope = $catchScopeResult->getScope();
				$finalScope = $catchScopeResult->isAlwaysTerminating() ? $finalScope : $catchScope->mergeWith($finalScope);
				$alwaysTerminating = $alwaysTerminating && $catchScopeResult->isAlwaysTerminating();
				if ($catchScopeResult->isAlwaysTerminating()) {
					$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $catchScopeResult->getAlwaysTerminatingStatements());
				}
				$finallyScope = $finallyScope->mergeWith($catchScope);
				foreach ($catchScopeResult->getExitPoints() as $exitPoint) {
					$finallyScope = $finallyScope->mergeWith($exitPoint->getScope());
					$exitPoints[] = $exitPoint;
				}
			}

			if ($finalScope === null) {
				$finalScope = $scope;
			}

			if ($stmt->finally !== null) {
				$originalFinallyScope = $finallyScope;
				$finallyResult = $this->processStmtNodes($stmt->finally->stmts, $finallyScope, $nodeCallback);
				$finallyScope = $finallyResult->getScope();
				$finalScope = $finallyResult->isAlwaysTerminating() ? $finalScope : $finalScope->processFinallyScope($finallyScope, $originalFinallyScope);
				if ($finallyResult->isAlwaysTerminating()) {
					$alwaysTerminatingStatements = array_merge($alwaysTerminatingStatements, $finallyResult->getAlwaysTerminatingStatements());
				}
			}

			// todo StatementResultTest
			return new StatementResult($finalScope, $alwaysTerminating ? $alwaysTerminatingStatements : [], $exitPoints);
		} elseif ($stmt instanceof Unset_) {
			foreach ($stmt->vars as $var) {
				$scope = $this->lookForEnterVariableAssign($scope, $var);
				$scope = $this->processExprNode($var, $scope, $nodeCallback, 1);
				$scope = $this->lookForExitVariableAssign($scope, $var);
				$scope = $scope->unsetExpression($var);
			}
		} elseif ($stmt instanceof Node\Stmt\Use_) {
			foreach ($stmt->uses as $use) {
				$this->processStmtNode($use, $scope, $nodeCallback);
			}
		} elseif ($stmt instanceof Node\Stmt\Global_) {
			foreach ($stmt->vars as $var) {
				if (!$var instanceof Variable) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				if (!is_string($var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$scope = $this->lookForEnterVariableAssign($scope, $var);
				$this->processExprNode($var, $scope, $nodeCallback, 1);
				$scope = $this->lookForExitVariableAssign($scope, $var);
				$scope = $scope->assignVariable($var->name, new MixedType());
			}
		} elseif ($stmt instanceof Static_) {
			foreach ($stmt->vars as $var) {
				$scope = $this->processStmtNode($var, $scope, $nodeCallback)->getScope();
			}
		} elseif ($stmt instanceof StaticVar) {
			if (!is_string($stmt->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if ($stmt->default !== null) {
				$this->processExprNode($stmt->default, $scope, $nodeCallback, 1);
			}
			$scope = $scope->enterExpressionAssign($stmt->var);
			$this->processExprNode($stmt->var, $scope, $nodeCallback, 1);
			$scope = $scope->exitExpressionAssign($stmt->var);
			$scope = $scope->assignVariable($stmt->var->name, new MixedType());
		} elseif ($stmt instanceof Node\Stmt\Const_ || $stmt instanceof Node\Stmt\ClassConst) {
			foreach ($stmt->consts as $const) {
				$nodeCallback($const, $scope);
				$this->processExprNode($const->value, $scope, $nodeCallback, 1);
			}
		}

		return new StatementResult($scope, [], []);
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
				foreach (array_merge([$referencedClass], $classReflection->getParentClassesNames()) as $className) {
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
	 * @param int $depth
	 * @return \PHPStan\Analyser\Scope $scope
	 */
	private function processExprNode(Expr $expr, Scope $scope, \Closure $nodeCallback, int $depth): Scope
	{
		if ($depth === 0 && !$scope->isInFirstLevelStatement()) {
			$scope = $scope->enterFirstLevelStatements();
		} elseif ($depth > 0 && $scope->isInFirstLevelStatement()) {
			$scope = $scope->exitFirstLevelStatements();
		}

		$nodeCallback($expr, $scope);

		// todo handle all expr descendants
		if ($expr instanceof Variable) {
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof Assign || $expr instanceof AssignRef) {
			if (!$expr->var instanceof Array_ && !$expr->var instanceof List_) {
				$scope = $this->processAssignVar(
					$scope,
					$expr->var,
					$expr->expr,
					$nodeCallback,
					function (Scope $scope) use ($expr, $nodeCallback, $depth): Scope {
						if ($expr instanceof AssignRef) {
							$scope = $scope->enterExpressionAssign($expr->expr);
						}

						if ($expr->expr instanceof Expr\Closure) {
							$assignedVariable = null;
							if ($expr->var instanceof Variable && is_string($expr->var->name)) {
								$assignedVariable = $expr->var->name;
							}
							$nodeCallback($expr->expr, $scope);
							$scope = $this->processClosureNode($expr->expr, $scope, $nodeCallback, $depth + 1, $assignedVariable);
						} else {
							$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth + 1);
						}

						if ($expr instanceof AssignRef) {
							$scope = $scope->exitExpressionAssign($expr->expr);
						}

						return $scope;
					},
					true
				);
			} else {
				$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, 1);
				foreach ($expr->var->items as $arrayItem) {
					if ($arrayItem === null) {
						continue;
					}

					$itemScope = $scope;
					if ($arrayItem->value instanceof ArrayDimFetch && $arrayItem->value->dim === null) {
						$itemScope = $itemScope->enterExpressionAssign($arrayItem->value);
					}
					$itemScope = $this->lookForEnterVariableAssign($itemScope, $arrayItem->value);

					$this->processExprNode($arrayItem, $itemScope, $nodeCallback, 1);
				}
				$scope = $this->lookForArrayDestructuringArray($scope, $expr->var, $scope->getType($expr->expr));
			}

			if ($expr->var instanceof Variable && is_string($expr->var->name)) {
				$comment = CommentHelper::getDocComment($expr);
				if ($comment !== null) {
					$scope = $this->processVarAnnotation($scope, $expr->var->name, $comment, false);
				}
			}
		} elseif ($expr instanceof Expr\AssignOp) {
			$scope = $this->processAssignVar(
				$scope,
				$expr->var,
				$expr,
				$nodeCallback,
				function (Scope $scope) use ($expr, $nodeCallback, $depth): Scope {
					return $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth + 1);
				},
				false
			);
		} elseif ($expr instanceof FuncCall) {
			$parametersAcceptor = null;
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $depth + 1);
			} elseif ($this->broker->hasFunction($expr->name, $scope)) {
				$function = $this->broker->getFunction($expr->name, $scope);
				$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
					$scope,
					$expr->args,
					$function->getVariants()
				);
			}
			$scope = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $depth);
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
					|| ($originalArrayType instanceof ArrayType && count($constantArrays) === 0)
				) {
					$arrayType = $originalArrayType;
					foreach ($argumentTypes as $argType) {
						$arrayType = $arrayType->setOffsetValueType(null, $argType);
					}

					$scope = $scope->specifyExpressionType($arrayArg, $arrayType);
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
				$expr->var instanceof Expr\Closure
				&& $expr->name instanceof Node\Identifier
				&& strtolower($expr->name->name) === 'call'
				&& isset($expr->args[0])
			) {
				$closureCallScope = $scope->enterClosureCall($scope->getType($expr->args[0]->value));
			}

			$scope = $this->processExprNode($expr->var, $closureCallScope ?? $scope, $nodeCallback, $depth + 1);
			if (isset($closureCallScope)) {
				$scope = $scope->restoreOriginalScopeAfterClosureBind($originalScope);
			}
			$parametersAcceptor = null;
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $depth + 1);
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
			$scope = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $depth);
		} elseif ($expr instanceof StaticCall) {
			if ($expr->class instanceof Expr) {
				$scope = $this->processExprNode($expr->class, $scope, $nodeCallback, $depth + 1);
			}

			$parametersAcceptor = null;
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $depth + 1);
			} elseif ($expr->class instanceof Name) {
				$className = $scope->resolveName($expr->class);
				if ($this->broker->hasClass($className)) {
					$classReflection = $this->broker->getClass($className);
					if ($classReflection->hasMethod($expr->name->name)) {
						$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
							$scope,
							$expr->args,
							$classReflection->getMethod($expr->name->name, $scope)->getVariants()
						);
						if (
							$classReflection->getName() === 'Closure'
							&& strtolower($expr->name->name) === 'bind'
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
			$scope = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $depth, $closureBindScope ?? null);
		} elseif ($expr instanceof PropertyFetch) {
			$scope = $this->processExprNode($expr->var, $scope, $nodeCallback, $depth + 1);
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof StaticPropertyFetch) {
			if ($expr->class instanceof Expr) {
				$scope = $this->processExprNode($expr->class, $scope, $nodeCallback, $depth + 1);
			}
			if ($expr->name instanceof Expr) {
				$scope = $this->processExprNode($expr->name, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof Expr\Closure) {
			$scope = $this->processClosureNode($expr, $scope, $nodeCallback, $depth, null);
		} elseif ($expr instanceof Expr\ClosureUse) {
			$this->processExprNode($expr->var, $scope, $nodeCallback, $depth);
		} elseif ($expr instanceof ErrorSuppress) {
			$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth);
		} elseif ($expr instanceof Exit_) {
			if ($expr->expr !== null) {
				$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof Node\Scalar\Encapsed) {
			foreach ($expr->parts as $part) {
				$scope = $this->processExprNode($part, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof ArrayDimFetch) {
			if ($expr->dim !== null) {
				$scope = $this->processExprNode($expr->dim, $scope, $nodeCallback, $depth + 1);
			}

			$scope = $this->processExprNode($expr->var, $scope, $nodeCallback, $depth + 1);
		} elseif ($expr instanceof Array_) {
			foreach ($expr->items as $arrayItem) {
				$scope = $this->processExprNode($arrayItem, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof ArrayItem) {
			if ($expr->key !== null) {
				$scope = $this->processExprNode($expr->key, $scope, $nodeCallback, $depth + 1);
			}
			$scope = $this->processExprNode($expr->value, $scope, $nodeCallback, $depth + 1);
		} elseif ($expr instanceof BooleanAnd || $expr instanceof BinaryOp\LogicalAnd) {
			$leftScope = $this->processExprNode($expr->left, $scope, $nodeCallback, $depth + 1);
			$rightScope = $this->processExprNode($expr->right, $leftScope->filterByTruthyValue($expr->left), $nodeCallback, $depth);

			return $leftScope->mergeWith($rightScope);
			// todo do not execute right side if the left is false
		} elseif ($expr instanceof BooleanOr || $expr instanceof BinaryOp\LogicalOr) {
			$leftScope = $this->processExprNode($expr->left, $scope, $nodeCallback, $depth + 1);
			$rightScope = $this->processExprNode($expr->right, $leftScope->filterByFalseyValue($expr->left), $nodeCallback, $depth);
			return $leftScope->mergeWith($rightScope);
			// todo do not execute right side if the left is true
		} elseif ($expr instanceof Coalesce) {
			$nonNullabilityResult = $this->ensureNonNullability($scope, $expr->left, false);

			if ($expr->left instanceof PropertyFetch) {
				$scope = $nonNullabilityResult->getScope();
			} else {
				$scope = $this->lookForEnterVariableAssign($nonNullabilityResult->getScope(), $expr->left);
			}
			$scope = $this->processExprNode($expr->left, $scope, $nodeCallback, $depth + 1);
			$scope = $this->revertNonNullability($scope, $nonNullabilityResult->getSpecifiedExpressions());

			if (!$expr->left instanceof PropertyFetch) {
				$scope = $this->lookForExitVariableAssign($scope, $expr->left);
			}
			$scope = $this->processExprNode($expr->right, $scope, $nodeCallback, $depth + 1);
		} elseif ($expr instanceof BinaryOp) {
			$scope = $this->processExprNode($expr->left, $scope, $nodeCallback, $depth + 1);
			$scope = $this->processExprNode($expr->right, $scope, $nodeCallback, $depth + 1);
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
			$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth + 1);
		} elseif ($expr instanceof BooleanNot) {
			$scope = $scope->enterNegation();
			$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth + 1);
			$scope = $scope->enterNegation();
		} elseif ($expr instanceof Expr\ClassConstFetch) {
			if ($expr->class instanceof Expr) {
				$scope = $this->processExprNode($expr->class, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof Expr\Empty_) {
			$nonNullabilityResult = $this->ensureNonNullability($scope, $expr->expr, true);
			$scope = $this->lookForEnterVariableAssign($nonNullabilityResult->getScope(), $expr->expr);
			$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth + 1);
			$scope = $this->revertNonNullability($scope, $nonNullabilityResult->getSpecifiedExpressions());
			$scope = $this->lookForExitVariableAssign($scope, $expr->expr);
		} elseif ($expr instanceof Expr\Isset_) {
			foreach ($expr->vars as $var) {
				$nonNullabilityResult = $this->ensureNonNullability($scope, $var, true);
				$scope = $this->lookForEnterVariableAssign($nonNullabilityResult->getScope(), $var);
				$scope = $this->processExprNode($var, $scope, $nodeCallback, $depth + 1);
				$scope = $this->revertNonNullability($scope, $nonNullabilityResult->getSpecifiedExpressions());
				$scope = $this->lookForExitVariableAssign($scope, $var);
			}
		} elseif ($expr instanceof Instanceof_) {
			$scope = $this->processExprNode($expr->expr, $scope, $nodeCallback, $depth + 1);
			if ($expr->class instanceof Expr) {
				$scope = $this->processExprNode($expr->class, $scope, $nodeCallback, $depth + 1);
			}
		} elseif ($expr instanceof List_) {
			// only in assign and foreach, processed elsewhere
			return $scope;
		} elseif ($expr instanceof New_) {
			$parametersAcceptor = null;
			if ($expr->class instanceof Expr) {
				$scope = $this->processExprNode($expr->class, $scope, $nodeCallback, $depth + 1);
			} elseif ($expr->class instanceof Class_) {
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
			$scope = $this->processArgs($parametersAcceptor, $expr->args, $scope, $nodeCallback, $depth);
		} elseif (
			$expr instanceof Expr\PreInc
			|| $expr instanceof Expr\PostInc
			|| $expr instanceof Expr\PreDec
			|| $expr instanceof Expr\PostDec
		) {
			$scope = $this->processExprNode($expr->var, $scope, $nodeCallback, $depth + 1);
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
						static function (Scope $scope): Scope {
							return $scope;
						},
						false
					);
				}
			}
		} elseif ($expr instanceof Ternary) {
			$scope = $this->processExprNode($expr->cond, $scope, $nodeCallback, $depth + 1);
			$ifTrueScope = $scope->filterByTruthyValue($expr->cond);
			$ifFalseScope = $scope->filterByFalseyValue($expr->cond);

			if ($expr->if !== null) {
				$ifTrueScope = $this->processExprNode($expr->if, $ifTrueScope, $nodeCallback, $depth);
				$ifFalseScope = $this->processExprNode($expr->else, $ifFalseScope, $nodeCallback, $depth);
			} else {
				$ifFalseScope = $this->processExprNode($expr->else, $ifFalseScope, $nodeCallback, $depth);
			}

			return $ifTrueScope->mergeWith($ifFalseScope);

			// todo do not run else if cond is always true
			// todo do not run if branch if cond is always false
		} elseif ($expr instanceof Expr\Yield_) {
			if ($expr->key !== null) {
				$scope = $this->processExprNode($expr->key, $scope, $nodeCallback, $depth + 1);
			}
			if ($expr->value !== null) {
				$scope = $this->processExprNode($expr->value, $scope, $nodeCallback, $depth + 1);
			}
		}

		return $scope;
	}

	/**
	 * @param \PhpParser\Node\Expr\Closure $expr
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param int $depth
	 * @param string $assignedVariable
	 * @return \PHPStan\Analyser\Scope $scope
	 */
	private function processClosureNode(
		Expr\Closure $expr,
		Scope $scope,
		\Closure $nodeCallback,
		int $depth,
		?string $assignedVariable
	): Scope
	{
		foreach ($expr->params as $param) {
			$this->processParamNode($param, $scope, $nodeCallback);
		}

		$byRefUses = [];
		$assignSelf = false;
		foreach ($expr->uses as $use) {
			if ($use->byRef) {
				$byRefUses[] = $use;
				$scope = $scope->enterExpressionAssign($use->var);
			}
			$this->processExprNode($use, $scope, $nodeCallback, $depth);
			if (!$use->byRef) {
				continue;
			}

			$scope = $scope->exitExpressionAssign($use->var);
			if ($assignedVariable !== $use->var->name) {
				continue;
			}

			$assignSelf = true;
		}

		if ($expr->returnType !== null) {
			$nodeCallback($expr->returnType, $scope);
		}

		if ($assignedVariable !== null && $assignSelf) {
			$scope = $scope->assignVariable($assignedVariable, $scope->getType($expr));
		}
		$closureScope = $scope->enterAnonymousFunction($expr);
		$closureScope = $closureScope->processClosureScope($scope, null, $byRefUses);
		if (count($byRefUses) === 0) {
			$this->processStmtNodes($expr->stmts, $closureScope, $nodeCallback);
			return $scope;
		}

		$count = 0;
		do {
			$prevScope = $closureScope;

			$intermediaryClosureScopeResult = $this->processStmtNodes($expr->stmts, $closureScope, static function (): void {
			});
			$intermediaryClosureScope = $intermediaryClosureScopeResult->getScope();
			foreach ($intermediaryClosureScopeResult->getExitPoints() as $exitPoint) {
				$intermediaryClosureScope = $intermediaryClosureScope->mergeWith($exitPoint->getScope());
			}
			$closureScope = $scope->enterAnonymousFunction($expr);
			$closureScope = $closureScope->processClosureScope($intermediaryClosureScope, $prevScope, $byRefUses);
			if ($closureScope->equals($prevScope)) {
				break;
			}
			$count++;
		} while ($count < self::LOOP_SCOPE_ITERATIONS);

		$this->processStmtNodes($expr->stmts, $closureScope, $nodeCallback);

		return $scope->processClosureScope($closureScope, null, $byRefUses);
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

		$this->processExprNode($param->default, $scope, $nodeCallback, 1);
	}

	/**
	 * @param ParametersAcceptor|null $parametersAcceptor
	 * @param \PhpParser\Node\Arg[] $args
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param int $depth
	 * @param \PHPStan\Analyser\Scope|null $closureBindScope
	 * @return \PHPStan\Analyser\Scope
	 */
	private function processArgs(
		?ParametersAcceptor $parametersAcceptor,
		array $args,
		Scope $scope,
		\Closure $nodeCallback,
		int $depth,
		?Scope $closureBindScope = null
	): Scope
	{
		// todo $scope = $scope->enterFunctionCall();
		if ($parametersAcceptor !== null) {
			$parameters = $parametersAcceptor->getParameters();
		}

		foreach ($args as $i => $arg) {
			$nodeCallback($arg, $scope);
			if (isset($parameters) && $parametersAcceptor !== null) {
				$assignByReference = false;
				if (isset($parameters[$i])) {
					$assignByReference = $parameters[$i]->passedByReference()->createsNewVariable();
				} elseif (count($parameters) > 0 && $parametersAcceptor->isVariadic()) {
					$lastParameter = $parameters[count($parameters) - 1];
					$assignByReference = $lastParameter->passedByReference()->createsNewVariable();
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
			$scope = $this->processExprNode($arg->value, $scopeToPass, $nodeCallback, $depth + 1);
			if ($i !== 0 || $closureBindScope === null) {
				continue;
			}

			$scope = $scope->restoreOriginalScopeAfterClosureBind($originalScope);
		}

		// todo $scope = $scope->exitFunctionCall();

		return $scope;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Expr $var
	 * @param \PhpParser\Node\Expr $assignedExpr
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param \Closure(Scope $scope): Scope $processExprCallback
	 * @param bool $enterExpressionAssign
	 * @return Scope
	 */
	private function processAssignVar(
		Scope $scope,
		Expr $var,
		Expr $assignedExpr,
		\Closure $nodeCallback,
		\Closure $processExprCallback,
		bool $enterExpressionAssign
	): Scope
	{
		$nodeCallback($var, $enterExpressionAssign ? $scope->enterExpressionAssign($var) : $scope);
		if ($var instanceof Variable && is_string($var->name)) {
			$scope = $processExprCallback($scope);
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
			$scope = $this->processExprNode($var, $scope, $nodeCallback, 1);
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
					$scope = $this->processExprNode($dimExpr, $scope, $nodeCallback, 1);

					if ($enterExpressionAssign) {
						$scope = $scope->exitExpressionAssign($dimExpr);
					}
				}
			}

			$valueToWrite = $scope->getType($assignedExpr);

			// 3. eval assigned expr
			$scope = $processExprCallback($scope);

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
			$scope = $processExprCallback($scope);
			$scope = $scope->specifyExpressionType($var, $scope->getType($assignedExpr));
		} elseif ($var instanceof Expr\StaticPropertyFetch) {
			$scope = $processExprCallback($scope);
			$scope = $scope->specifyExpressionType($var, $scope->getType($assignedExpr));
		}

		return $scope;
	}

	private function processVarAnnotation(Scope $scope, string $variableName, string $comment, bool $strict): Scope
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
			return $scope->assignVariable($variableName, $variableType);

		}

		if (!$strict && count($varTags) === 1 && isset($varTags[0])) {
			$variableType = $varTags[0]->getType();
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
			$itemTypes = [];
			$exprType = $scope->getType($stmt->expr);
			$arrayTypes = TypeUtils::getArrays($exprType);
			foreach ($arrayTypes as $arrayType) {
				$itemTypes[] = $arrayType->getItemType();
			}

			$itemType = count($itemTypes) > 0 ? TypeCombinator::union(...$itemTypes) : new MixedType();
			$scope = $this->lookForArrayDestructuringArray($scope, $stmt->valueVar, $itemType);
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
				$this->processStmtNodes($node->stmts, $scope->enterTrait($traitReflection), $nodeCallback);
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
	 * @return mixed[]
	 */
	private function getPhpDocs(Scope $scope, Node\FunctionLike $functionLike): array
	{
		$phpDocParameterTypes = [];
		$phpDocReturnType = null;
		$phpDocThrowType = null;
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
			$isDeprecated = $resolvedPhpDoc->isDeprecated();
			$isInternal = $resolvedPhpDoc->isInternal();
			$isFinal = $resolvedPhpDoc->isFinal();
		}

		return [$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $isDeprecated, $isInternal, $isFinal];
	}

}
