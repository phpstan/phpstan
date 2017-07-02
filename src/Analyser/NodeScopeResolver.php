<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Broker\Broker;
use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CommentHelper;
use PHPStan\Type\CommonUnionType;
use PHPStan\Type\FalseBooleanType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NestedArrayItemType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\TrueBooleanType;
use PHPStan\Type\TrueOrFalseBooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\VoidType;

class NodeScopeResolver
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\File\FileExcluder */
	private $fileExcluder;

	/** @var \PhpParser\BuilderFactory */
	private $builderFactory;

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var bool */
	private $polluteScopeWithLoopInitialAssignments;

	/** @var bool */
	private $polluteCatchScopeWithTryAssignments;

	/** @var string[][] className(string) => methods(string[]) */
	private $earlyTerminatingMethodCalls;

	/** @var \PHPStan\Reflection\ClassReflection|null */
	private $anonymousClassReflection;

	/** @var bool[] filePath(string) => bool(true) */
	private $analysedFiles;

	/** @var null|bool */
	private $scopeFilteringMode;

	/** @var int */
	private $exprLevel = 0;

	/** @var null|self */
	private static $instance;

	public function __construct(
		Broker $broker,
		Parser $parser,
		\PhpParser\PrettyPrinter\Standard $printer,
		FileTypeMapper $fileTypeMapper,
		FileExcluder $fileExcluder,
		\PhpParser\BuilderFactory $builderFactory,
		FileHelper $fileHelper,
		bool $polluteScopeWithLoopInitialAssignments,
		bool $polluteCatchScopeWithTryAssignments,
		array $earlyTerminatingMethodCalls
	)
	{
		$this->broker = $broker;
		$this->parser = $parser;
		$this->printer = $printer;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->fileExcluder = $fileExcluder;
		$this->builderFactory = $builderFactory;
		$this->fileHelper = $fileHelper;
		$this->polluteScopeWithLoopInitialAssignments = $polluteScopeWithLoopInitialAssignments;
		$this->polluteCatchScopeWithTryAssignments = $polluteCatchScopeWithTryAssignments;
		$this->earlyTerminatingMethodCalls = $earlyTerminatingMethodCalls;

		self::$instance = $this;
	}

	public static function getInstance(): self
	{
		assert(self::$instance !== null);
		return self::$instance;
	}

	/**
	 * @param string[] $files
	 */
	public function setAnalysedFiles(array $files)
	{
		$this->analysedFiles = array_fill_keys($files, true);
	}

	/**
	 * @param \PhpParser\Node[] $nodes
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure $nodeCallback
	 * @param \PHPStan\Analyser\Scope $closureBindScope
	 * @return \PHPStan\Analyser\Scope
	 */
	public function processNodes(
		array $nodes,
		Scope $scope,
		\Closure $nodeCallback,
		Scope $closureBindScope = null
	): Scope
	{
		foreach ($nodes as $node) {
			if (!($node instanceof \PhpParser\Node)) {
				continue;
			}

			$scope = $this->processNode($node, $scope, $nodeCallback);
		}

		return $scope;
	}

	private function processNodesX(array $nodes, Scope $scope, callable $nodeCallback, Node &$earlyTerminationNode = null)
	{
		$earlyTerminationNode = null;

		foreach ($nodes as $node) {
			$scope = $this->processNode($node, $scope, $nodeCallback);

			if ($node instanceof Return_ || $node instanceof Throw_ || $node instanceof Exit_ || $node instanceof Continue_ || $node instanceof Break_) {
				$earlyTerminationNode = $node;
				break;
			}
		}

		return $scope;
	}

	public function getExprType(Expr $node, Scope $scope): Type
	{
		$scope = $this->processExprNode($node, $scope, function () {}, $resultType);
		return $resultType;
	}

	private function processNode(Node $node, Scope $scope, callable $nodeCallback): Scope
	{
		if ($node instanceof Stmt) {
			$nodeCallback($node, $scope);
			if ($node instanceof Declare_) {
				return $this->processDeclareNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Namespace_) {
				return $this->processNamespaceNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Function_) {
				return $this->processFunctionNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Class_) {
				return $this->processClassNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Interface_) {
				return $this->processInterfaceNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Property) {
				return $this->processPropertyNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof ClassMethod) {
				return $this->processClassMethodNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof If_) {
				return $this->processIfNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Switch_) {
				return $this->processSwitchNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof While_) {
				return $this->processWhileNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Do_) {
				return $this->processDoWhileNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof For_) {
				return $this->processForNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Foreach_) {
				return $this->processForeachNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof TryCatch) {
				return $this->processTryCatchNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Static_) {
				return $this->processStaticNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Unset_) {
				return $this->processUnsetNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Echo_) {
				return $this->processEchoNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Return_) {
				return $this->processReturnNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Throw_) {
				return $this->processThrowNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Continue_) {
				return $this->processContinueNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Break_) {
				return $this->processBreakNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Use_ || $node instanceof GroupUse || $node instanceof Nop || $node instanceof ClassConst) {
				return $scope; // ignore
			}

		} elseif ($node instanceof Expr) {
			return $this->processExprNode($node, $scope, $nodeCallback);

		} else {
			$nodeCallback($node, $scope);
			if ($node instanceof Arg) {
				return $this->processArgNode($node, $scope, $nodeCallback);

			} elseif ($node instanceof Param) {
				return $this->processParamNode($node, $scope, $nodeCallback);
			}
		}

		throw new \Exception(sprintf('Unsupported node %s', get_class($node)));
	}

	private function processExprNode(Expr $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$nodeCallback($node, $scope);

		if ($scope->isSpecified($node)) {
			$resultType = $scope->getSpecifiedType($node);
			return $scope;
		}

		if ($node instanceof BinaryOp) {
			return $this->processBinaryOpNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof AssignOp) {
			return $this->processAssignOpNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Ternary) {
			return $this->processTernaryNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Scalar) {
			return $this->processScalarNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Array_) {
			return $this->processArrayNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof New_) {
			return $this->processNewNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Clone_) {
			return $this->processCloneNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Expr\Closure) {
			return $this->processClosureNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof ConstFetch) {
			return $this->processConstFetchNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof ClassConstFetch) {
			return $this->processClassConstFetchNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Variable) {
			return $this->processVariableNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof FuncCall) {
			return $this->processFuncCallNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof MethodCall) {
			return $this->processMethodCallNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof StaticCall) {
			return $this->processStaticCallNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof PropertyFetch) {
			return $this->processPropertyFetchNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof StaticPropertyFetch) {
			return $this->processStaticPropertyFetchNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof ArrayDimFetch) {
			return $this->processArrayDimFetchNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Assign) {
			return $this->processAssignNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof AssignRef) {
			return $this->processAssignRefNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Instanceof_) {
			return $this->processInstanceofNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Isset_) {
			return $this->processIssetNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Empty_) {
			return $this->processEmptyNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Exit_) {
			return $this->processExitNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Yield_) {
			return $this->processYieldNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Print_) {
			return $this->processPrintNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof BooleanNot) {
			return $this->processBooleanNotNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof UnaryMinus) {
			return $this->processUnaryMinusNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof UnaryPlus) {
			return $this->processUnaryPlusNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof PostInc) {
			return $this->processPostIncNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof PostDec) {
			return $this->processPostDecNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof PreInc) {
			return $this->processPreIncNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof PreDec) {
			return $this->processPreDecNode($node, $scope, $nodeCallback, $resultType);

		} elseif ($node instanceof Cast) {
			return $this->processCastNode($node, $scope, $nodeCallback, $resultType);
		}

		throw new \Exception(sprintf('Unsupported Expr node %s', get_class($node)));
	}

	private function processDeclareNode(Declare_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		foreach ($node->declares as $declare) {
			if (
				$declare instanceof Node\Stmt\DeclareDeclare
				&& $declare->key === 'strict_types'
				&& $declare->value instanceof Node\Scalar\LNumber
				&& $declare->value->value === 1
			) {
				$scope = $scope->enterDeclareStrictTypes();
				break;
			}
		}

		return $scope;
	}

	private function processNamespaceNode(Namespace_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		$scope = $scope->enterNamespace((string) $node->name);
		$scope = $this->processNodes($node->stmts, $scope, $nodeCallback);

		return $scope;
	}

	private function processFunctionNode(Function_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		list($phpDocParameterTypes, $phpDocReturnType) = $this->getPhpDocs($scope, $node);

		$scope = $scope->enterFunction($node, $phpDocParameterTypes, $phpDocReturnType);
		$scope = $this->processNodes($node->params, $scope, $nodeCallback);
		$scope = $this->processNodes($node->stmts, $scope, $nodeCallback);

		return $scope;
	}

	private function processClassNode(Class_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		if (isset($node->namespacedName)) {
			$classReflection = $this->broker->getClass((string) $node->namespacedName);
			$classScope = $scope->enterClass($classReflection);
			$this->processNodes($node->stmts, $classScope, $nodeCallback);

		} elseif ($node->name === null) {
			$code = $this->printer->prettyPrint([$node]);
			$classReflection = new \ReflectionClass(eval(sprintf('return new %s;', $code)));
			$classReflection = $this->broker->getClassFromReflection(
				$classReflection,
				sprintf('class@anonymous%s:%s', $scope->getFile(), $node->getLine())
			);

			$classScope = $scope->enterAnonymousClass($classReflection);
			$this->processNodes($node->stmts, $classScope, $nodeCallback);

		} else {
			throw new \Exception();
		}

		return $scope;
	}

	private function processInterfaceNode(Interface_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		assert(isset($node->namespacedName));
		$classReflection = $this->broker->getClass((string) $node->namespacedName);

		$classScope = $scope->enterClass($classReflection);
		$this->processNodes($node->stmts, $classScope, $nodeCallback);

		return $scope;
	}

	private function processPropertyNode(Property $node, Scope $scope, callable $nodeCallback): Scope
	{
		foreach ($node->props as $prop) {
			$nodeCallback($prop, $scope);
		}

		return $scope;
	}

	private function processClassMethodNode(ClassMethod $node, Scope $scope, callable $nodeCallback): Scope
	{
		list($phpDocParameterTypes, $phpDocReturnType) = $this->getPhpDocs($scope, $node);

		$scope = $scope->enterClassMethod($node, $phpDocParameterTypes, $phpDocReturnType);

		if ($node->stmts !== null) {
			$scope = $this->processNodes($node->stmts, $scope, $nodeCallback);
		}

		return $scope;
	}

	private function processIfNode(If_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		$scope = $this->processExprNode($node->cond, $scope, $nodeCallback, $condType);

		$branchScope = $scope->filterByTruthyValue($node->cond);
		$branchScope = $this->processNodesX($node->stmts, $branchScope, $nodeCallback, $earlyTerminated);
		$finalScope = $earlyTerminated ? null : $branchScope;

		$scope = $scope->filterByFalseyValue($node->cond);

		foreach ($node->elseifs as $elseIfNode) {
			$scope = $this->processExprNode($elseIfNode->cond, $scope, $nodeCallback, $condType2);

			$branchScope = $scope->filterByTruthyValue($elseIfNode->cond);
			$branchScope = $this->processNodesX($elseIfNode->stmts, $branchScope, $nodeCallback, $earlyTerminated);
			$finalScope = $earlyTerminated ? $finalScope : $branchScope->mergeWith($finalScope);

			$scope = $scope->filterByFalseyValue($elseIfNode->cond);
		}

		if ($node->else === null) {
			$finalScope = $scope->mergeWith($finalScope);

		} else {
			$branchScope = $this->processNodesX($node->else->stmts, $scope, $nodeCallback, $earlyTerminated);
			$finalScope = $earlyTerminated ? $finalScope : $branchScope->mergeWith($finalScope);
		}

		return $finalScope ?? $scope; // TODO: how to handle empty finalScope? Should propagate earlyTermination up?
	}

	private function processSwitchNode(Switch_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		$scope = $this->processExprNode($node->cond, $scope, $nodeCallback, $condType);

		$finalScope = null;
		$prevScope = null;
		$hasDefaultCase = false;

		foreach ($node->cases as $i => $caseNode) {
			if ($caseNode->cond !== null) {
				$condExpr = new BinaryOp\Equal($node->cond, $caseNode->cond);

				$branchScope = $this->processExprNode($caseNode->cond, $scope, $nodeCallback, $condType);
				$branchScope = $branchScope->filterByTruthyValue($condExpr);
				$scope = $scope->filterByFalseyValue($condExpr);

			} else {
				$hasDefaultCase = true;

				// none of the previous cases matched
				$branchScope = $scope;

				// none of the following cases matched
				for ($j = $i + 1; $j < count($node->cases); $j++) {
					$branchScope = $branchScope->filterByFalseyValue($node->cases[$j]->cond);
				}
			}

			$branchScope = $branchScope->mergeWith($prevScope);
			$branchScope = $this->processNodesX($caseNode->stmts, $branchScope, $nodeCallback, $earlyTerminationNode);
			$prevScope = $earlyTerminationNode ? null : $branchScope;

			$switchTerminated = $earlyTerminationNode instanceof Continue_ || $earlyTerminationNode instanceof Break_;
			$isLastCase = ($i === count($node->cases) - 1);
			if ($switchTerminated || ($isLastCase && $earlyTerminationNode === null)) {
				$finalScope = $branchScope->mergeWith($finalScope);
			}
		}

		if (!$hasDefaultCase || $finalScope === null) {
			$finalScope = $scope->mergeWith($finalScope);
		}

		return $finalScope;
	}

	private function processWhileNode(While_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		$scope = $this->processExprNode($node->cond, $scope, $nodeCallback, $condType);

		$finalScope = null;
		$bodyScope = $scope;
		$count = 0;

		do {
			$prevScope = $bodyScope;
			$bodyScope = $prevScope->filterByTruthyValue($node->cond);
			$bodyScope = $this->processNodesX($node->stmts, $bodyScope, $nodeCallback, $earlyTerminated);
			$finalScope = $earlyTerminated ? $finalScope : $bodyScope->mergeWith($finalScope);
			$count++;

		} while (!$earlyTerminated && !$bodyScope->isEqual($prevScope) && $count < 5);

		$scope = $scope->filterByFalseyValue($node->cond);
		$finalScope = $scope->mergeWith($finalScope);

		return $finalScope;
	}

	private function processDoWhileNode(Do_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		$scope = $this->processNodesX($node->stmts, $scope, $nodeCallback, $earlyTerminationNode);
		$scope = $this->processExprNode($node->cond, $scope, $nodeCallback, $condType);
		$scope = $scope->filterByFalseyValue($node->cond);

		return $scope;
	}

	private function processForNode(For_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		foreach ($node->init as $initExpr) {
			$scope = $this->processExprNode($initExpr, $scope, $nodeCallback, $condType);
		}

		$branchScope = $scope;
		foreach ($node->cond as $condExpr) {
			$branchScope = $this->processExprNode($condExpr, $branchScope, $nodeCallback, $condType);
			$branchScope = $branchScope->filterByTruthyValue($condExpr);
			$scope = $scope->filterByFalseyValue($condExpr);
		}

		$branchScope = $this->processNodesX($node->stmts, $branchScope, $nodeCallback, $earlyTerminationNode);

		foreach ($node->loop as $loopExpr) {
			$branchScope = $this->processExprNode($loopExpr, $branchScope, $nodeCallback, $loopExprType);
		}

		$scope = $scope->mergeWith($branchScope);

		return $scope;
	}

	private function processForeachNode(Foreach_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);

		if ($node->valueVar instanceof Variable && is_string($node->valueVar->name)) {
			$scope = $scope->enterForeach(
				$node->expr,
				$node->valueVar->name,
				$node->keyVar !== null
				&& $node->keyVar instanceof Variable
				&& is_string($node->keyVar->name)
					? $node->keyVar->name
					: null
			);

		} elseif ($node->keyVar !== null && $node->keyVar instanceof Variable && is_string($node->keyVar->name)) {
			$scope = $scope->assignVariable($node->keyVar->name);
		}

		if ($node->valueVar instanceof List_ || $node->valueVar instanceof Array_) {
			$scope = $this->lookForArrayDestructuringArray($scope, $node->valueVar);
		}

		$branchScope = $this->processNodes($node->stmts, $scope, $nodeCallback);
		$finalScope = $scope->mergeWith($branchScope);

		return $finalScope;
	}

	private function processTryCatchNode(TryCatch $node, Scope $scope, callable $nodeCallback): Scope
	{
		$branchScope = $this->processNodesX($node->stmts, $scope, $nodeCallback, $earlyTerminated);
		$finalScope = $earlyTerminated ? null : $branchScope;

		foreach ($node->catches as $catchNode) {
			$nodeCallback($catchNode, $scope);

			$exceptionTypes = [];
			foreach ($catchNode->types as $catchNodeTypeName) {
				$exceptionTypes[] = new ObjectType((string) $catchNodeTypeName);
			}

			$exceptionType = count($exceptionTypes) > 1 ? new CommonUnionType($exceptionTypes) : $exceptionTypes[0];
			$branchScope = $scope->assignVariable($catchNode->var, $exceptionType);
			$branchScope = $this->processNodesX($catchNode->stmts, $branchScope, $nodeCallback, $earlyTerminated);
			$finalScope = $earlyTerminated ? $finalScope : $branchScope->mergeWith($finalScope);
		}

		if ($node->finally !== null) {
			$finalScope = $this->processNodesX($node->finally->stmts, $finalScope, $nodeCallback, $earlyTerminated);
		}

		return $finalScope;
	}

	private function processStaticNode(Static_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		foreach ($node->vars as $staticVar) {
			if ($staticVar->default !== null) {
				$scope = $this->processExprNode($staticVar->default, $scope, $nodeCallback, $valueType);
				$scope = $scope->assignVariable($staticVar->name, $valueType);

			} else {
				$scope = $scope->assignVariable($staticVar->name, new NullType());
			}
		}

		return $scope;
	}

	private function processUnsetNode(Unset_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		foreach ($node->vars as $var) {
			if ($var instanceof Variable && is_string($var->name)) {
				$scope = $scope->unsetVariable($var->name);
			}
		}

		return $scope;
	}

	private function processEchoNode(Echo_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		foreach ($node->exprs as $expr) {
			$scope = $this->processExprNode($expr, $scope, $nodeCallback, $exprType);
		}

		return $scope;
	}

	private function processReturnNode(Return_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		if ($node->expr !== null) {
			$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);
		}

		return $scope;
	}

	private function processThrowNode(Throw_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);

		return $scope;
	}

	private function processContinueNode(Continue_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		if ($node->num instanceof Expr) {
			$scope = $this->processExprNode($node->num, $scope, $nodeCallback, $exprType);
		}

		return $scope;
	}

	private function processBreakNode(Break_ $node, Scope $scope, callable $nodeCallback): Scope
	{
		if ($node->num instanceof Expr) {
			$scope = $this->processExprNode($node->num, $scope, $nodeCallback, $exprType);
		}

		return $scope;
	}

	private function processConstFetchNode(ConstFetch $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$constName = strtolower((string) $node->name);
		if ($constName === 'true') {
			$resultType = new TrueBooleanType();

		} elseif ($constName === 'false') {
			$resultType = new FalseBooleanType();

		} elseif ($constName === 'null') {
			$resultType = new NullType();

		} elseif ($this->broker->hasConstant($node->name, $scope)) {
			$constName = $this->broker->resolveConstantName($node->name, $scope);
			$constValue = constant($constName);
			$resultType = $scope->getTypeFromValue($constValue); // TODO

		} else {
			$resultType = new MixedType();
		}

		return $scope;
	}

	private function processClassConstFetchNode(ClassConstFetch $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$resultType = new MixedType();

		if ($node->class instanceof Expr) {
			$scope = $this->processExprNode($node->class, $scope, $nodeCallback, $classType);
			$className = $classType->getClass();

		} elseif ($node->class instanceof Name) {
			if ($scope->isInClass()) {
				$className = $scope->resolveName($node->class);

			} else {
				$className = (string) $node->class;
			}
		}

		if (isset($className) && is_string($node->name)) {
			$constantName = $node->name;

			if ($constantName === 'class') {
				$resultType = new StringType();

			} elseif ($this->broker->hasClass($className)) {
				$classReflection = $this->broker->getClass($className);
				if ($classReflection->hasConstant($constantName)) {
					$constantValue = $classReflection->getConstant($constantName)->getValue();
					$resultType = $scope->getTypeFromValue($constantValue);
				}
			}
		}

		return $scope;
	}

	private function processVariableNode(Variable $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if (is_string($node->name)) {
			if ($scope->hasVariableType($node->name)) {
				$resultType = $scope->getVariableType($node->name);

			} else {
				$resultType = new MixedType(); // TODO: use ErrorType
			}

		} else {
			$scope = $this->processExprNode($node->name, $scope, $nodeCallback, $nameType);
			$resultType = new MixedType();
		}

		return $scope;
	}

	private function processFuncCallNode(FuncCall $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if ($node->name instanceof Expr) {
			$scope = $this->processExprNode($node->name, $scope, $nodeCallback, $nameType);
			$scope = $this->processNodes($node->args, $scope, $nodeCallback);
			$resultType = new MixedType();

		} elseif ($node->name instanceof Name) {
			$functionName = (string) $node->name;

			if ($functionName === 'assert') {
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);
				$scope = $scope->filterByTruthyValue($node->args[0]->value);
				$resultType = new TrueOrFalseBooleanType();

			} elseif (count($node->args) > 0 && $functionName === 'array_map' && $node->args[0]->value instanceof Expr\Closure) {
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);
				$closure = $node->args[0]->value;
				$closureReturnType = $scope->getFunctionType($closure->returnType, $closure->returnType === null, false);
				$resultType = new ArrayType($closureReturnType, true);

			} elseif (count($node->args) > 1 && $functionName === 'array_reduce' && $node->args[1]->value instanceof Expr\Closure) {
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);
				$closure = $node->args[1]->value;
				$closureReturnType = $scope->getFunctionType($closure->returnType, $closure->returnType === null, false);
				$resultType = $closureReturnType;

			} elseif (count($node->args) > 0 && ($functionName === 'array_filter' || $functionName === 'array_unique' || $functionName === 'array_reverse')) {
				$scope = $this->processArgNodes($node->args, $scope, $nodeCallback, $argumentValueTypes);
				$resultType = $argumentValueTypes[0];

			} elseif (count($node->args) > 2 && $functionName === 'array_fill') {
				$scope = $this->processArgNodes($node->args, $scope, $nodeCallback, $argumentValueTypes);
				$resultType = new ArrayType($argumentValueTypes[2]);

			} elseif (count($node->args) > 1 && $functionName === 'array_fill_keys') {
				$scope = $this->processArgNodes($node->args, $scope, $nodeCallback, $argumentValueTypes);
				$resultType = new ArrayType($argumentValueTypes[1]);

			} elseif (count($node->args) > 0 && ($functionName === 'min' || $functionName === 'max')) {
				if (count($node->args) === 1) {
					$scope = $this->processExprNode($node->args[0]->value, $scope, $nodeCallback, $argumentType);
					if ($argumentType instanceof ArrayType) {
						$resultType = $argumentType->getItemType();
					}

				} else {
					$resultType = null;
					foreach ($node->args as $arg) {
						$scope = $this->processExprNode($arg->value, $scope, $nodeCallback, $argumentType);
						if ($resultType === null) {
							$resultType = $argumentType;
						} else {
							$resultType = $resultType->combineWith($argumentType);
						}
					}
				}

			} elseif ($this->broker->hasFunction($node->name, $scope)) {
				$functionReflection = $this->broker->getFunction($node->name, $scope);
				$parameterReflections = $functionReflection->getParameters();

				foreach ($node->args as $i => $arg) {
					if (isset($parameterReflections[$i])
						&& $parameterReflections[$i]->isPassedByReference()
						&& $arg->value instanceof Variable
						&& is_string($arg->value->name)
						&& !$scope->hasVariableType($arg->value->name)
					) {
						$scope = $scope->assignVariable($arg->value->name, new NullType());
					}

					$scope = $this->processExprNode($arg->value, $scope, $nodeCallback, $argumentType);
				}

				$resultType = $functionReflection->getReturnType();

			} else {
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);
				$resultType = new MixedType();
			}

		} else {
			throw new \Exception('Unsupported');
		}

		return $scope;
	}

	private function processMethodCallNode(MethodCall $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $varType);
		$resultType = new MixedType();
		assert($varType instanceof Type);

		if ($node->name instanceof Expr) {
			$scope = $this->processExprNode($node->name, $scope, $nodeCallback, $nameType);
			$scope = $this->processNodes($node->args, $scope, $nodeCallback);

		} elseif (is_string($node->name)) {
			$methodName = $node->name;

			if ($varType->getClass() !== null) {
				$className = $varType->getClass();
				if ($this->broker->hasClass($className)) {
					$classReflection = $this->broker->getClass($className);
					if ($classReflection->hasMethod($methodName)) {
						$methodReflection = $classReflection->getMethod($methodName, $scope);
						$parameterReflections = $methodReflection->getParameters();

						foreach ($node->args as $i => $arg) {
							if (isset($parameterReflections[$i])
								&& $parameterReflections[$i]->isPassedByReference()
								&& $arg->value instanceof Variable
								&& is_string($arg->value->name)
								&& !$scope->hasVariableType($arg->value->name)
							) {
								$scope = $scope->assignVariable($arg->value->name, new NullType());
							}

							$scope = $this->processExprNode($arg->value, $scope, $nodeCallback, $argumentType);
						}

						$resultType = $methodReflection->getReturnType();

						foreach ($this->broker->getDynamicMethodReturnTypeExtensionsForClass($className) as $dynamicMethodReturnTypeExtension) {
							if ($dynamicMethodReturnTypeExtension->isMethodSupported($methodReflection)) {
								$resultType = $dynamicMethodReturnTypeExtension->getTypeFromMethodCall($methodReflection, $node, $scope);
								break;
							}
						}

						if ($resultType instanceof StaticResolvableType) {
							$calledOnThis = $node->var instanceof Variable && is_string($node->var->name) && $node->var->name === 'this';
							if ($calledOnThis) {
								if ($scope->isInClass()) {
									$resultType = $resultType->changeBaseClass($scope->getClassReflection()->getName());
								}
							} else {
								$resultType = $resultType->resolveStatic($className);
							}
						}
					}
				}
			}

			if (!isset($parameterReflections)) {
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);
			}

		} else {
			throw new \Exception();
		}

		return $scope;
	}

	private function processStaticCallNode(StaticCall $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$resultType = new MixedType();

		if ($node->class instanceof Expr) {
			$scope = $this->processExprNode($node->class, $scope, $nodeCallback, $classType);
			assert($classType instanceof Type);
			$className = $classType->getClass();

		} elseif ($node->class instanceof Name) {
			$className = $scope->resolveName($node->class);

		} else {
			throw new \Exception();
		}

		if ($node->name instanceof Expr) {
			$scope = $this->processExprNode($node->name, $scope, $nodeCallback, $nameType);
			$scope = $this->processNodes($node->args, $scope, $nodeCallback);

		} elseif (is_string($node->name)) {
			$methodName = $node->name;

			/*if ($className === 'Closure' && $methodName === 'bind' && count($node->args) > 2 && $node->args[0]->value instanceof Expr\Closure) {
				$argTypes = [];
				for ($i = 1; $i < count($node->args); $i++) {
					$scope = $this->processArgNode($node->args[$i], $scope, $nodeCallback, $argTypes[$i]);
				}

				if ($argTypes[1] instanceof NullType) {

				}

				$closureThisType = $argTypes[1] instanceof NullType ? $argTypes[2] : $argTypes[1];
				$closureScope =
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);

//				$scope->enterClosureBind()

			} else*/if ($className !== null && $this->broker->hasClass($className)) {
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);

				if ($this->broker->getClass($className)) {
					$classReflection = $this->broker->getClass($className);
					if ($classReflection->hasMethod($methodName)) {
						$methodReflection = $classReflection->getMethod($methodName, $scope);
						$resultType = $methodReflection->getReturnType();

						foreach ($this->broker->getDynamicStaticMethodReturnTypeExtensionsForClass($className) as $dynamicStaticMethodReturnTypeExtension) {
							if ($dynamicStaticMethodReturnTypeExtension->isStaticMethodSupported($methodReflection)) {
								$resultType = $dynamicStaticMethodReturnTypeExtension->getTypeFromStaticMethodCall($methodReflection, $node, $scope);
								break;
							}
						}

						if ($resultType instanceof StaticResolvableType) {
							if ($node->class instanceof Name) {
								$nodeClassString = strtolower((string) $node->class);
								if ($scope->isInClass() && in_array($nodeClassString, ['self', 'static', 'parent'], true)) {
									$resultType = $resultType->changeBaseClass($scope->getClassReflection()->getName());
								}
							} else {
								$resultType = $resultType->resolveStatic($className);
							}
						}
					}
				}

			} else {
				$scope = $this->processNodes($node->args, $scope, $nodeCallback);
			}

		} else {
			throw new \Exception();
		}

		return $scope;
	}

	private function processPropertyFetchNode(PropertyFetch $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $varType);
		$resultType = new MixedType();
		assert($varType instanceof Type);

		if ($node->name instanceof Expr) {
			$scope = $this->processExprNode($node->name, $scope, $nodeCallback, $nameType);

		} elseif (is_string($node->name)) {
			$propertyName = $node->name;

			if ($varType->getClass() !== null) {
				$className = $varType->getClass();
				if ($this->broker->hasClass($className)) {
					$classReflection = $this->broker->getClass($className);
					if ($classReflection->hasProperty($propertyName)) {
						$propertyReflection = $classReflection->getProperty($propertyName, $scope);
						$resultType = $propertyReflection->getType();
					}
				}
			}

		} else {
			throw new \Exception();
		}

		return $scope;
	}

	private function processStaticPropertyFetchNode(StaticPropertyFetch $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$resultType = new MixedType();

		if ($node->class instanceof Expr) {
			$scope = $this->processExprNode($node->class, $scope, $nodeCallback, $classType);
			assert($classType instanceof Type);
			$className = $classType->getClass();

		} elseif ($node->class instanceof Name) {
			if ($scope->isInClass()) {
				$className = $scope->resolveName($node->class);

			} else {
				$className = (string) $node->class;
			}

		} else {
			throw new \Exception();
		}

		if ($node->name instanceof Expr) {
			$scope = $this->processExprNode($node->name, $scope, $nodeCallback, $nameType);

		} elseif (is_string($node->name)) {
			$propertyName = $node->name;

			if ($className !== null && $this->broker->hasClass($className)) {
				if ($this->broker->getClass($className)) {
					$classReflection = $this->broker->getClass($className);
					if ($classReflection->hasProperty($propertyName)) {
						$propertyReflection = $classReflection->getProperty($propertyName, $scope);
						$resultType = $propertyReflection->getType();
					}
				}
			}

		} else {
			throw new \Exception();
		}

		return $scope;
	}

	private function processArrayDimFetchNode(ArrayDimFetch $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $varType);

		if ($node->dim instanceof Expr) {
			$scope = $this->processExprNode($node->dim, $scope, $nodeCallback, $dimType);

		} elseif ($node->dim === null) {
			throw new \LogicException('ArrayDimFetch: WRITE MODE?');

		} else {
			throw new \LogicException();
		}

		if ($varType instanceof ArrayType) {
			$resultType = $varType->getItemType();

		} else {
			$resultType = new MixedType();
		}

		return $scope;
	}

	private function processAssignNode(Assign $node, Scope $scope, callable $nodeCallback, Type &$resultType = null): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $assignedType);

		if ($this->tryExtractVariableTypeFromPhpDoc($node, $scope, $extractedType)) {
			$assignedType = $extractedType;
		}

		$resultType = $assignedType;

		if ($node->var instanceof Variable) {
			if (is_string($node->var->name)) {
				// $scope = $scope->enterVariableAssign($node->var->name);
				$scope = $scope->assignVariable($node->var->name, $assignedType);
			}

		} elseif ($node->var instanceof Array_ || $node->var instanceof List_) {
			$scope = $this->lookForArrayDestructuringArray($scope, $node->var);

		} elseif ($node->var instanceof ArrayDimFetch) {
			$depth = 0;
			$var = $node->var;
			while ($var instanceof ArrayDimFetch) {
				$var = $var->var;
				$depth++;
			}

			if ($var instanceof Variable && is_string($var->name)) {
				if ($scope->hasVariableType($var->name)) {
					$arrayDimFetchVariableType = $scope->getVariableType($var->name);
					if (
						!$arrayDimFetchVariableType instanceof ArrayType
						&& !$arrayDimFetchVariableType instanceof MixedType
					) {
						return $scope;
					}
				}
				$arrayType = ArrayType::createDeepArrayType(
					new NestedArrayItemType($assignedType, $depth),
					false
				);

				if ($scope->hasVariableType($var->name)) {
					$arrayType = $scope->getVariableType($var->name)->combineWith($arrayType);
				}

				$scope = $scope->assignVariable($var->name, $arrayType);
			}

		} elseif ($node->var instanceof PropertyFetch) {
			$scope = $this->processExprNode($node->var->var, $scope, $nodeCallback, $objectType);

			if ($node->var->name instanceof Expr) {
				$scope = $this->processExprNode($node->var->name, $scope, $nodeCallback, $nameType);
			}

			$scope = $scope->specifyExpressionType($node->var, $assignedType);

		} elseif ($node->var instanceof StaticPropertyFetch) {
			if ($node->var->class instanceof Expr) {
				$scope = $this->processExprNode($node->var->class, $scope, $nodeCallback, $classType);
			}

			if ($node->var->name instanceof Expr) {
				$scope = $this->processExprNode($node->var->name, $scope, $nodeCallback, $nameType);
			}

			$scope = $scope->specifyExpressionType($node->var, $assignedType);

		} else {
			throw new \Exception(sprintf('Unsupported Assign node %s', get_class($node->var)));
		}

		return $scope;
	}

	private function processAssignRefNode(AssignRef $node, Scope $scope, callable $nodeCallback, Type &$resultType = null): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $assignedType);

		if ($this->tryExtractVariableTypeFromPhpDoc($node, $scope, $extractedType)) {
			$assignedType = $extractedType;
		}

		$resultType = $assignedType;

		if ($node->var instanceof Variable) {
			if (is_string($node->var->name)) {
				$scope = $scope->assignVariable($node->var->name, $assignedType);
			}

		} else {
			throw new \Exception(sprintf('Unsupported AssignRef node %s', get_class($node->var)));
		}

		return $scope;
	}

	private function processInstanceofNode(Instanceof_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);

		if ($node->class instanceof Expr) {
			$scope = $this->processExprNode($node->class, $scope, $nodeCallback, $classType);

		} elseif ($node->class instanceof Name) {
			// TODO
		}

		$resultType = new TrueOrFalseBooleanType();

		return $scope;
	}

	private function processIssetNode(Isset_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$issetScope = $scope;
		$issetScope = $issetScope->enterContext(Scope::CONTEXT_ISSET);

		foreach ($node->vars as $varNode) {
			$issetScope = $this->processExprNode($varNode, $issetScope, $nodeCallback, $varType);
		}

		$issetScope = $issetScope->exitContext(Scope::CONTEXT_ISSET);
		$resultType = new TrueOrFalseBooleanType();

		return $issetScope;
	}

	private function processEmptyNode(Empty_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$emptyScope = $scope;
		$emptyScope = $emptyScope->enterContext(Scope::CONTEXT_EMPTY);
		$emptyScope = $this->processExprNode($node->expr, $emptyScope, $nodeCallback, $exprType);
		$emptyScope = $emptyScope->exitContext(Scope::CONTEXT_EMPTY);

		$resultType = new TrueOrFalseBooleanType();

		return $emptyScope;
	}

	private function processExitNode(Exit_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if ($node->expr !== null) {
			$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);
		}

		// TODO: $resultType = new NeverType();
		$resultType = new VoidType();

		return $scope;
	}

	private function processYieldNode(Yield_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if ($node->key) {
			$scope = $this->processExprNode($node->key, $scope, $nodeCallback, $keyType);
		}

		if ($node->value) {
			$scope = $this->processExprNode($node->value, $scope, $nodeCallback, $keyType);
		}

		$resultType = new MixedType();

		return $scope;
	}

	private function processPrintNode(Print_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);
		$resultType = new IntegerType();

		return $scope;
	}

	private function processBooleanNotNode(BooleanNot $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);
		$resultType = new TrueOrFalseBooleanType();

		return $scope;
	}

	private function processUnaryMinusNode(UnaryMinus $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);

		if ($exprType instanceof IntegerType || $exprType instanceof FloatType) {
			$resultType = $exprType;

		} else {
			$resultType = new CommonUnionType([
				new IntegerType(),
				new FloatType()
			]);
		}

		return $scope;
	}

	private function processUnaryPlusNode(UnaryPlus $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);

		if ($exprType instanceof IntegerType || $exprType instanceof FloatType) {
			$resultType = $exprType;

		} else {
			$resultType = new CommonUnionType([
				new IntegerType(),
				new FloatType()
			]);
		}

		return $scope;
	}

	private function processPostIncNode(PostInc $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $varType);
		$resultType = $varType;

		return $scope;
	}

	private function processPostDecNode(PostDec $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $varType);
		$resultType = $varType;

		return $scope;
	}

	private function processPreIncNode(PreInc $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $varType);
		$resultType = $varType;

		return $scope;
	}

	private function processPreDecNode(PreDec $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $varType);
		$resultType = $varType;

		return $scope;
	}

	private function processCastNode(Cast $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);

		if ($node instanceof Cast\Bool_) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof Cast\Int_) {
			$resultType = new IntegerType();

		} elseif ($node instanceof Cast\Double) {
			$resultType = new FloatType();

		} elseif ($node instanceof Cast\String_) {
			$resultType = new StringType();

		} elseif ($node instanceof Cast\Array_) {
			$resultType = new ArrayType(new MixedType());

		} elseif ($node instanceof Cast\Object_) {
			if ($exprType instanceof ObjectType) {
				$resultType = $exprType; // TODO: This is improvement

			} else {
				$resultType = new ObjectType('stdClass');
			}

		} elseif ($node instanceof Cast\Unset_) {
			$resultType = new NullType();

		} else {
			$resultType = new MixedType();
		}

		return $scope;
	}

	private function processBinaryOpNode(BinaryOp $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if ($node instanceof BinaryOp\BooleanAnd) {
			$leftScope = $this->processExprNode($node->left, $scope, $nodeCallback, $leftType);
			$rightScope = $leftScope->filterByTruthyValue($node->left);
			$rightScope = $this->processExprNode($node->right, $rightScope, $nodeCallback, $rightType);

			$resultType = new TrueOrFalseBooleanType();
			return $leftScope->mergeWith($rightScope);

		} elseif ($node instanceof BooleanOr) {
			$leftScope = $this->processExprNode($node->left, $scope, $nodeCallback, $leftType);
			$rightScope = $leftScope->filterByFalseyValue($node->left);
			$rightScope = $this->processExprNode($node->right, $rightScope, $nodeCallback, $rightType);

			$resultType = new TrueOrFalseBooleanType();
			return $leftScope->mergeWith($rightScope);

		} elseif ($node instanceof Coalesce) {
			$condExpr = new BinaryOp\NotIdentical($node->left, new ConstFetch(new Name('null')));

			$leftScope = $scope;
			$leftScope = $leftScope->enterContext(Scope::CONTEXT_ISSET);
			$leftScope = $leftScope->filterByTruthyValue($condExpr);
			$leftScope = $this->processExprNode($node->left, $leftScope, $nodeCallback, $leftType);
			$leftScope = $leftScope->exitContext(Scope::CONTEXT_ISSET);

			$rightScope = $scope;
			$rightScope = $rightScope->enterContext(Scope::CONTEXT_ISSET);
//			$rightScope = $rightScope->filterByFalseyValue($condExpr);
			$rightScope = $this->processExprNode($node->left, $rightScope, $nodeCallback);
			$rightScope = $rightScope->exitContext(Scope::CONTEXT_ISSET);
			$rightScope = $this->processExprNode($node->right, $rightScope, $nodeCallback, $rightType);

			$resultType = $leftType->combineWith($rightType);
			return $leftScope->mergeWith($rightScope);
		}

		$scope = $this->processExprNode($node->left, $scope, $nodeCallback, $leftType);
		$scope = $this->processExprNode($node->right, $scope, $nodeCallback, $rightType);

		if ($leftType instanceof BooleanType) {
			$leftType = new IntegerType();
		}

		if ($rightType instanceof BooleanType) {
			$rightType = new IntegerType();
		}

		if ($node instanceof BinaryOp\Plus) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof ArrayType && $rightType instanceof ArrayType) {
				$resultType = new ArrayType($leftType->getItemType()->combineWith($rightType->getItemType()));

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof BinaryOp\Minus) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof BinaryOp\Mul) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof BinaryOp\Div) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new FloatType(); // TODO: union

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof BinaryOp\Pow) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof BinaryOp\Mod) {
			$resultType = new IntegerType();

		} elseif ($node instanceof BinaryOp\Concat) {
			$resultType = new StringType();

		} elseif ($node instanceof BinaryOp\Spaceship) {
			$resultType = new IntegerType();

		} elseif ($node instanceof BinaryOp\Identical) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\NotIdentical) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\Equal) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\NotEqual) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\Greater) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\GreaterOrEqual) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\Smaller) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\SmallerOrEqual) {
			$resultType = new TrueOrFalseBooleanType();

		} elseif ($node instanceof BinaryOp\LogicalXor) {
			$resultType = new TrueOrFalseBooleanType();

		} else {
			throw new \Exception(sprintf('Unsupported Expr node %s', get_class($node)));
			$resultType = new MixedType();
		}

		return $scope;
	}

	private function processAssignOpNode(AssignOp $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->var, $scope, $nodeCallback, $leftType);
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $rightType);

		if ($node instanceof AssignOp\Plus) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof ArrayType && $rightType instanceof ArrayType) {
				$resultType = new ArrayType($leftType->getItemType()->combineWith($rightType->getItemType()));

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof AssignOp\Minus) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof AssignOp\Mul) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof AssignOp\Div) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new FloatType(); // TODO: union

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof AssignOp\Pow) {
			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				$resultType = new IntegerType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} elseif ($leftType instanceof FloatType && $rightType instanceof FloatType) {
				$resultType = new FloatType();

			} else {
				$resultType = new MixedType();
			}

		} elseif ($node instanceof AssignOp\Mod) {
			$resultType = new IntegerType();

		} else {
			throw new \Exception(sprintf('Unsupported Expr node %s', get_class($node)));
		}

		if ($node->var instanceof Variable && is_string($node->var->name)) {
			$scope = $scope->assignVariable($node->var->name, $resultType);
		}

		return $scope;
	}

	private function processTernaryNode(Ternary $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->cond, $scope, $nodeCallback, $condType);

		$ifTrueScope = $scope->filterByTruthyValue($node->cond);
		$ifFalseScope = $scope->filterByFalseyValue($node->cond);

		if ($node->if !== null) {
			$ifTrueScope = $this->processExprNode($node->if, $ifTrueScope, $nodeCallback, $ifTrueResultType);
			$ifFalseScope = $this->processExprNode($node->else, $ifFalseScope, $nodeCallback, $ifFalseResultType);

		} else {
			$ifTrueResultType = TypeCombinator::removeNull($condType);
			$ifFalseScope = $this->processExprNode($node->else, $ifFalseScope, $nodeCallback, $ifFalseResultType);
		}

		$resultType = $ifTrueResultType->combineWith($ifFalseResultType);
		$scope = $ifTrueScope->mergeWith($ifFalseScope);

		return $scope;
	}

	private function processScalarNode(Scalar $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if ($node instanceof Scalar\LNumber) {
			$resultType = new IntegerType();

		} elseif ($node instanceof Scalar\DNumber) {
			$resultType = new FloatType();

		} elseif ($node instanceof Scalar\String_) {
			$resultType = new StringType();

		} elseif ($node instanceof Scalar\Encapsed) {
			foreach ($node->parts as $partExpr) {
				if (!$partExpr instanceof Scalar\EncapsedStringPart) { // Call to undefined method PhpParser\PrettyPrinter\Standard::pScalar_EncapsedStringPart()
					$scope = $this->processExprNode($partExpr, $scope, $nodeCallback, $partExprType);
				}
			}
			$resultType = new StringType();

		} elseif ($node instanceof Scalar\EncapsedStringPart) {
			$resultType = new StringType();

		} elseif ($node instanceof Scalar\MagicConst\Line) {
			$resultType = new IntegerType();

		} elseif ($node instanceof Scalar\MagicConst) {
			$resultType = new StringType();

		} else {
			throw new \Exception(sprintf('Unsupported Scalar node %s', get_class($node)));
		}

		return $scope;
	}

	private function processArrayNode(Array_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$valueTypes = [];
		foreach ($node->items as $item) {
			if ($item->key !== null) {
				$scope = $this->processExprNode($item->key, $scope, $nodeCallback, $keyType);
			}

			if ($item->byRef) {
				if ($item->value instanceof Variable && is_string($item->value->name) && !$scope->hasVariableType($item->value->name)) {
					$scope = $scope->assignVariable($item->value->name, new NullType());
				}
			}

			$scope = $this->processExprNode($item->value, $scope, $nodeCallback, $valueType);
			$valueTypes[] = $valueType;
		}

		$possiblyCallable = false;
		if (count($valueTypes) === 2) {
			if ($valueTypes[0] instanceof ObjectType || $valueTypes[0] instanceof StringType) {
				if ($valueTypes[1] instanceof StringType) {
					$possiblyCallable = true;
				}
			}
		}

		$resultType = new ArrayType(TypeCombinator::union($valueTypes), true, $possiblyCallable);

		return $scope;
	}

	private function processNewNode(New_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if ($node->class instanceof Name) {
			$className = (string) $node->class;

			if ($scope->isInClass() && $className === 'static') {
				$resultType = new StaticType($scope->getClassReflection()->getName());

			} elseif ($scope->isInClass() && $className === 'self') {
				$resultType = new ObjectType($scope->getClassReflection()->getName());

			} else {
				$resultType = new ObjectType($className);
			}

		} elseif ($node->class instanceof Expr) {
			$scope = $this->processExprNode($node->class, $scope, $nodeCallback, $classType);
			$resultType = new MixedType();

		} elseif ($node->class instanceof Class_) {
			$this->processNode($node->class, $scope, $nodeCallback);
			$resultType = new MixedType();

		} else {
			throw new \Exception(sprintf('Unsupported node %s', get_class($node)));
		}

		$scope = $this->processNodes($node->args, $scope, $nodeCallback);

		return $scope;
	}

	private function processCloneNode(Clone_ $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$scope = $this->processExprNode($node->expr, $scope, $nodeCallback, $exprType);
		$resultType = $exprType;

		return $scope;
	}

	private function processClosureNode(Expr\Closure $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		foreach ($node->uses as $use) {
			if ($use->byRef && !$scope->hasVariableType($use->var)) {
				$scope = $scope->assignVariable($use->var, new NullType());
			}
		}

		$closureScope = $scope->enterAnonymousFunction($node->params, $node->uses, $node->returnType);
		$closureScope = $this->processNodes($node->stmts, $closureScope, $nodeCallback);

		$resultType = new ObjectType('Closure');

		return $scope;
	}

	/**
	 * @param Arg[] $nodes
	 */
	private function processArgNodes(array $nodes, Scope $scope, callable $nodeCallback, array &$resultTypes = NULL): Scope
	{
		$resultTypes = [];
		foreach ($nodes as $node) {
			$scope = $this->processArgNode($node, $scope, $nodeCallback, $argValueType);
			$resultTypes[] = $argValueType;
		}

		return $scope;
	}

	private function processArgNode(Arg $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		if ($node->byRef) {
			if ($node->value instanceof Variable && is_string($node->value->name) && !$scope->hasVariableType($node->value->name)) {
				$scope = $scope->assignVariable($node->value->name, new NullType());
			}

		} else {
			$scope = $this->processExprNode($node->value, $scope, $nodeCallback, $valueType);
			$resultType = $valueType;
		}

		return $scope;
	}

	private function processParamNode(Param $node, Scope $scope, callable $nodeCallback): Scope
	{
		if ($node->default !== null) {
			$this->processExprNode($node->default, $scope, $nodeCallback, $defaultValueType);
		}

		return $scope;
	}

//	/**
//	 * @param \PhpParser\Node[] $nodes
//	 * @param \PHPStan\Analyser\Scope $scope
//	 * @param \Closure $nodeCallback
//	 * @param \PHPStan\Analyser\Scope $closureBindScope
//	 */
//	public function processNodes(
//		array $nodes,
//		Scope $scope,
//		\Closure $nodeCallback,
//		Scope $closureBindScope = null
//	)
//	{
//		foreach ($nodes as $i => $node) {
//			if (!($node instanceof \PhpParser\Node)) {
//				continue;
//			}
//
//			if ($scope->getInFunctionCall() !== null && $node instanceof Arg) {
//				$functionCall = $scope->getInFunctionCall();
//				$value = $node->value;
//
//				$parametersAcceptor = $this->findParametersAcceptorInFunctionCall($functionCall, $scope);
//
//				if ($parametersAcceptor !== null) {
//					$parameters = $parametersAcceptor->getParameters();
//					$assignByReference = false;
//					if (isset($parameters[$i])) {
//						$assignByReference = $parameters[$i]->isPassedByReference();
//					} elseif (count($parameters) > 0 && $parametersAcceptor->isVariadic()) {
//						$lastParameter = $parameters[count($parameters) - 1];
//						$assignByReference = $lastParameter->isPassedByReference();
//					}
//					if ($assignByReference && $value instanceof Variable && is_string($value->name)) {
//						$scope = $scope->assignVariable($value->name, new MixedType());
//					}
//				}
//			}
//
//			$nodeScope = $scope;
//			if ($i === 0 && $closureBindScope !== null) {
//				$nodeScope = $closureBindScope;
//			}
//
//			$this->processNode($node, $nodeScope, $nodeCallback);
//			$scope = $this->lookForAssigns($scope, $node);
//
//			if ($node instanceof If_) {
//				if ($this->findEarlyTermination($node->stmts, $scope) !== null) {
//					$scope = $scope->filterByFalseyValue($node->cond);
//					$this->processNode($node->cond, $scope, function (Node $node, Scope $inScope) use (&$scope) {
//						$this->specifyFetchedPropertyForInnerScope($node, $inScope, true, $scope);
//					});
//				}
//			} elseif ($node instanceof Node\Stmt\Declare_) {
//				foreach ($node->declares as $declare) {
//					if (
//						$declare instanceof Node\Stmt\DeclareDeclare
//						&& $declare->key === 'strict_types'
//						&& $declare->value instanceof Node\Scalar\LNumber
//						&& $declare->value->value === 1
//					) {
//						$scope = $scope->enterDeclareStrictTypes();
//						break;
//					}
//				}
//			} elseif (
//				$node instanceof FuncCall
//				&& $node->name instanceof Name
//				&& (string) $node->name === 'assert'
//				&& isset($node->args[0])
//			) {
//				$scope = $scope->filterByTruthyValue($node->args[0]->value);
//			}
//		}
//	}
//
	private function specifyProperty(Scope $scope, Expr $expr): Scope
	{
		if ($expr instanceof PropertyFetch) {
			return $scope->specifyFetchedPropertyFromIsset($expr);

		} elseif (
			$expr instanceof Expr\StaticPropertyFetch
			&& $expr->class instanceof Name
			&& (string) $expr->class === 'static'
		) {
			return $scope->specifyFetchedStaticPropertyFromIsset($expr);

		} else {
			$scope = $scope->specifyExpressionType($expr, new MixedType());
		}

		return $scope;
	}
//
//	private function specifyFetchedPropertyForInnerScope(Node $node, Scope $inScope, bool $inEarlyTermination, Scope &$scope)
//	{
//		if ($inEarlyTermination === $inScope->isNegated()) {
//			if ($node instanceof Isset_) {
//				foreach ($node->vars as $var) {
//					$scope = $this->specifyProperty($scope, $var);
//				}
//			}
//		} else {
//			if ($node instanceof Expr\Empty_) {
//				$scope = $this->specifyProperty($scope, $node->expr);
//				$scope = $this->assignVariable($scope, $node->expr);
//			}
//		}
//	}
//
	private function lookForArrayDestructuringArray(Scope $scope, Node $node): Scope
	{
		if ($node instanceof Array_) {
			foreach ($node->items as $item) {
				$scope = $this->lookForArrayDestructuringArray($scope, $item->value);
			}
		} elseif ($node instanceof Variable && is_string($node->name)) {
			$scope = $scope->assignVariable($node->name);
		} elseif ($node instanceof ArrayDimFetch && $node->var instanceof Variable && is_string($node->var->name)) {
			$scope = $scope->assignVariable($node->var->name);
		} elseif ($node instanceof List_) {
			if (isset($node->items)) {
				$nodeItems = $node->items;
			} elseif (isset($node->vars)) {
				$nodeItems = $node->vars;
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}
			foreach ($nodeItems as $item) {
				if ($item === null) {
					continue;
				}
				$itemValue = $item;
				if ($itemValue instanceof ArrayItem) {
					$itemValue = $itemValue->value;
				}
				if ($itemValue instanceof Variable && is_string($itemValue->name)) {
					$scope = $scope->assignVariable($itemValue->name);
				} else {
					$scope = $this->lookForArrayDestructuringArray($scope, $itemValue);
				}
			}
		}

		return $scope;
	}
//
//	private function enterForeach(Scope $scope, Foreach_ $node): Scope
//	{
//		if ($node->valueVar instanceof Variable && is_string($node->valueVar->name)) {
//			$scope = $scope->enterForeach(
//				$node->expr,
//				$node->valueVar->name,
//				$node->keyVar !== null
//				&& $node->keyVar instanceof Variable
//				&& is_string($node->keyVar->name)
//					? $node->keyVar->name
//					: null
//			);
//		}
//
//		if ($node->keyVar !== null && $node->keyVar instanceof Variable && is_string($node->keyVar->name)) {
//			$scope = $scope->assignVariable($node->keyVar->name);
//		}
//
//		if ($node->valueVar instanceof List_ || $node->valueVar instanceof Array_) {
//			$scope = $this->lookForArrayDestructuringArray($scope, $node->valueVar);
//		}
//
//		return $this->lookForAssigns($scope, $node->valueVar);
//	}
//
//	private function processNode(\PhpParser\Node $node, Scope $scope, \Closure $nodeCallback)
//	{
//		$nodeCallback($node, $scope);
//
//		if (
//			$node instanceof \PhpParser\Node\Stmt\ClassLike
//		) {
//			if ($node instanceof Node\Stmt\Trait_) {
//				return;
//			}
//			if (isset($node->namespacedName)) {
//				$scope = $scope->enterClass($this->broker->getClass((string) $node->namespacedName));
//			} elseif ($this->anonymousClassReflection !== null) {
//				$scope = $scope->enterAnonymousClass($this->anonymousClassReflection);
//			} else {
//				throw new \PHPStan\ShouldNotHappenException();
//			}
//		} elseif ($node instanceof Node\Stmt\TraitUse) {
//			$this->processTraitUse($node, $scope, $nodeCallback);
//		} elseif ($node instanceof \PhpParser\Node\Stmt\Function_) {
//			$scope = $this->enterFunction($scope, $node);
//		} elseif ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
//			$scope = $this->enterClassMethod($scope, $node);
//		} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
//			$scope = $scope->enterNamespace((string) $node->name);
//		} elseif (
//			$node instanceof \PhpParser\Node\Expr\StaticCall
//			&& (is_string($node->class) || $node->class instanceof \PhpParser\Node\Name)
//			&& is_string($node->name)
//			&& (string) $node->class === 'Closure'
//			&& $node->name === 'bind'
//		) {
//			$thisType = null;
//			if (isset($node->args[1])) {
//				$argValue = $node->args[1]->value;
//				if ($argValue instanceof Expr\ConstFetch && ((string) $argValue->name === 'null')) {
//					$thisType = null;
//				} else {
//					$thisType = $scope->getType($argValue);
//				}
//			}
//			$scopeClass = 'static';
//			if (isset($node->args[2])) {
//				$argValue = $node->args[2]->value;
//				$argValueType = $scope->getType($argValue);
//				if ($argValueType->getClass() !== null) {
//					$scopeClass = $argValueType->getClass();
//				} elseif (
//					$argValue instanceof Expr\ClassConstFetch
//					&& $argValue->name === 'class'
//					&& $argValue->class instanceof Name
//				) {
//					$scopeClass = $scope->resolveName($argValue->class);
//				} elseif ($argValue instanceof Node\Scalar\String_) {
//					$scopeClass = $argValue->value;
//				}
//			}
//			$closureBindScope = $scope->enterClosureBind($thisType, $scopeClass);
//		} elseif ($node instanceof \PhpParser\Node\Expr\Closure) {
//			$scope = $scope->enterAnonymousFunction($node->params, $node->uses, $node->returnType);
//		} elseif ($node instanceof Foreach_) {
//			$scope = $this->enterForeach($scope, $node);
//		} elseif ($node instanceof Catch_) {
//			if (isset($node->types)) {
//				$nodeTypes = $node->types;
//			} elseif (isset($node->type)) {
//				$nodeTypes = [$node->type];
//			} else {
//				throw new \PHPStan\ShouldNotHappenException();
//			}
//			$scope = $scope->enterCatch(
//				$nodeTypes,
//				$node->var
//			);
//		} elseif ($node instanceof For_) {
//			foreach ($node->init as $initExpr) {
//				$scope = $this->lookForAssigns($scope, $initExpr);
//			}
//
//			foreach ($node->cond as $condExpr) {
//				$scope = $this->lookForAssigns($scope, $condExpr);
//			}
//
//			foreach ($node->loop as $loopExpr) {
//				$scope = $this->lookForAssigns($scope, $loopExpr);
//			}
//		} elseif ($node instanceof Array_) {
//			$scope = $scope->exitFirstLevelStatements();
//			foreach ($node->items as $item) {
//				$this->processNode($item, $scope, $nodeCallback);
//				if ($item->key !== null) {
//					$scope = $this->lookForAssigns($scope, $item->key);
//				}
//				$scope = $this->lookForAssigns($scope, $item->value);
//			}
//
//			return;
//		} elseif ($node instanceof If_) {
//			$scope = $this->lookForAssigns($scope, $node->cond)->exitFirstLevelStatements();
//			$ifScope = $scope;
//			$this->processNode($node->cond, $scope, $nodeCallback);
//			$scope = $scope->filterByTruthyValue($node->cond);
//
//			$specifyFetchedProperty = function (Node $node, Scope $inScope) use (&$scope) {
//				$this->specifyFetchedPropertyForInnerScope($node, $inScope, false, $scope);
//			};
//			$this->processNode($node->cond, $scope, $specifyFetchedProperty);
//			$this->processNodes($node->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);
//
//			$elseifScope = $ifScope->filterByFalseyValue($node->cond);
//			foreach ($node->elseifs as $elseif) {
//				$scope = $elseifScope;
//				$scope = $this->lookForAssigns($scope, $elseif->cond)->exitFirstLevelStatements();
//				$this->processNode($elseif->cond, $scope, $nodeCallback);
//				$scope = $scope->filterByTruthyValue($elseif->cond);
//				$this->processNode($elseif->cond, $scope, $specifyFetchedProperty);
//				$this->processNodes($elseif->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);
//				$elseifScope = $this->lookForAssigns($elseifScope, $elseif->cond)
//					->filterByFalseyValue($elseif->cond);
//			}
//			if ($node->else !== null) {
//				$this->processNode($node->else, $elseifScope, $nodeCallback);
//			}
//
//			return;
//		} elseif ($node instanceof Switch_) {
//			$scope = $scope->exitFirstLevelStatements();
//			$this->processNode($node->cond, $scope, $nodeCallback);
//			$scope = $this->lookForAssigns($scope, $node->cond);
//			$switchScope = $scope;
//			$switchConditionIsTrue = $node->cond instanceof Expr\ConstFetch && strtolower((string) $node->cond->name) === 'true';
//			$switchConditionGetClassExpression = null;
//			if (
//				$node->cond instanceof FuncCall
//				&& $node->cond->name instanceof Name
//				&& strtolower((string) $node->cond->name) === 'get_class'
//				&& isset($node->cond->args[0])
//			) {
//				$switchConditionGetClassExpression = $node->cond->args[0]->value;
//			}
//			foreach ($node->cases as $caseNode) {
//				if ($caseNode->cond !== null) {
//					$switchScope = $this->lookForAssigns($switchScope, $caseNode->cond);
//
//					if ($switchConditionIsTrue) {
//						$switchScope = $switchScope->filterByTruthyValue($caseNode->cond);
//					} elseif (
//						$switchConditionGetClassExpression !== null
//						&& $caseNode->cond instanceof Expr\ClassConstFetch
//						&& $caseNode->cond->class instanceof Name
//						&& strtolower($caseNode->cond->name) === 'class'
//					) {
//						$switchScope = $switchScope->specifyExpressionType(
//							$switchConditionGetClassExpression,
//							new ObjectType((string) $caseNode->cond->class)
//						);
//					}
//				}
//				$this->processNode($caseNode, $switchScope, $nodeCallback);
//				if ($this->findEarlyTermination($caseNode->stmts, $switchScope) !== null) {
//					$switchScope = $scope;
//				}
//			}
//			return;
//		} elseif ($node instanceof While_) {
//			$scope = $this->lookForAssigns($scope, $node->cond);
//		} elseif ($node instanceof TryCatch) {
//			$statements = [];
//			$this->processNodes($node->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);
//			if ($this->polluteCatchScopeWithTryAssignments) {
//				foreach ($node->stmts as $statement) {
//					$scope = $this->lookForAssigns($scope, $statement);
//				}
//			}
//
//			if (isset($node->finally) || isset($node->finallyStmts)) {
//				$statements[] = new StatementList($scope, $node->stmts, true);
//			}
//
//			foreach ($node->catches as $catch) {
//				$this->processNode($catch, $scope, $nodeCallback);
//				if (isset($node->finally) || isset($node->finallyStmts)) {
//					if (isset($catch->types)) {
//						$catchTypes = $catch->types;
//					} elseif (isset($catch->type)) {
//						$catchTypes = [$catch->type];
//					} else {
//						throw new \PHPStan\ShouldNotHappenException();
//					}
//					$statements[] = new StatementList($scope->enterCatch(
//						$catchTypes,
//						$catch->var
//					), $catch->stmts, true);
//				}
//			}
//
//			if (isset($node->finally) || isset($node->finallyStmts)) {
//				$finallyScope = $this->lookForAssignsInBranches($scope, $statements);
//
//				if (isset($node->finally)) {
//					$this->processNode($node->finally, $finallyScope, $nodeCallback);
//				} elseif (isset($node->finallyStmts)) {
//					$this->processNodes($node->finallyStmts, $finallyScope, $nodeCallback);
//				}
//			}
//
//			return;
//		} elseif ($node instanceof Ternary) {
//			$scope = $this->lookForAssigns($scope, $node->cond);
//		} elseif ($node instanceof Do_) {
//			foreach ($node->stmts as $statement) {
//				$scope = $this->lookForAssigns($scope, $statement);
//			}
//		} elseif ($node instanceof FuncCall) {
//			$scope = $scope->enterFunctionCall($node);
//		} elseif ($node instanceof Expr\StaticCall) {
//			$scope = $scope->enterFunctionCall($node);
//		} elseif ($node instanceof MethodCall) {
//			if (
//				$scope->getType($node->var)->getClass() === 'Closure'
//				&& $node->name === 'call'
//				&& isset($node->args[0])
//			) {
//				$closureCallScope = $scope->enterClosureBind($scope->getType($node->args[0]->value), 'static');
//			}
//			$scope = $scope->enterFunctionCall($node);
//		} elseif ($node instanceof Array_) {
//			foreach ($node->items as $item) {
//				$scope = $this->lookForAssigns($scope, $item->value);
//			}
//		} elseif ($node instanceof New_ && $node->class instanceof Class_) {
//			$node->args = [];
//			foreach ($node->class->stmts as $i => $statement) {
//				if (
//					$statement instanceof Node\Stmt\ClassMethod
//					&& $statement->name === '__construct'
//				) {
//					unset($node->class->stmts[$i]);
//					$node->class->stmts = array_values($node->class->stmts);
//					break;
//				}
//			}
//
//			$node->class->stmts[] = $this->builderFactory
//				->method('__construct')
//				->makePublic()
//				->getNode();
//
//			$code = $this->printer->prettyPrint([$node]);
//			$classReflection = new \ReflectionClass(eval(sprintf('return %s', $code)));
//			$this->anonymousClassReflection = $this->broker->getClassFromReflection(
//				$classReflection,
//				sprintf('class@anonymous%s:%s', $scope->getFile(), $node->getLine())
//			);
//		} elseif ($node instanceof BooleanNot) {
//			$scope = $scope->enterNegation();
//		} elseif ($node instanceof Unset_ || $node instanceof Isset_) {
//			foreach ($node->vars as $unsetVar) {
//				if ($unsetVar instanceof Variable && is_string($unsetVar->name)) {
//					$scope = $scope->enterVariableAssign($unsetVar->name);
//				}
//			}
//		}
//
//		$originalScope = $scope;
//		foreach ($node->getSubNodeNames() as $subNodeName) {
//			$scope = $originalScope;
//			$subNode = $node->{$subNodeName};
//
//			if (is_array($subNode)) {
//				$argClosureBindScope = null;
//				if (isset($closureBindScope) && $subNodeName === 'args') {
//					$argClosureBindScope = $closureBindScope;
//				}
//				if ($subNodeName === 'stmts') {
//					$scope = $scope->enterFirstLevelStatements();
//				} else {
//					$scope = $scope->exitFirstLevelStatements();
//				}
//
//				if ($node instanceof Foreach_ && $subNodeName === 'stmts') {
//					$scope = $this->lookForAssigns($scope, $node->expr);
//				}
//				if ($node instanceof While_ && $subNodeName === 'stmts') {
//					$scope = $scope->filterByTruthyValue($node->cond);
//				}
//
//				if ($node instanceof Isset_ && $subNodeName === 'vars') {
//					foreach ($node->vars as $issetVar) {
//						$scope = $this->specifyProperty($scope, $issetVar);
//					}
//				}
//
//				if ($node instanceof MethodCall && $subNodeName === 'args') {
//					$scope = $this->lookForAssigns($scope, $node->var);
//				}
//
//				$this->processNodes($subNode, $scope, $nodeCallback, $argClosureBindScope);
//			} elseif ($subNode instanceof \PhpParser\Node) {
//				if ($node instanceof Coalesce && $subNodeName === 'left') {
//					$scope = $this->assignVariable($scope, $subNode);
//				}
//
//				if ($node instanceof Ternary) {
//					if ($subNodeName === 'if') {
//						$scope = $scope->filterByTruthyValue($node->cond);
//						$this->processNode($node->cond, $scope, function (Node $node, Scope $inScope) use (&$scope) {
//							$this->specifyFetchedPropertyForInnerScope($node, $inScope, false, $scope);
//						});
//					} elseif ($subNodeName === 'else') {
//						$scope = $scope->filterByFalseyValue($node->cond);
//						$this->processNode($node->cond, $scope, function (Node $node, Scope $inScope) use (&$scope) {
//							$this->specifyFetchedPropertyForInnerScope($node, $inScope, true, $scope);
//						});
//					}
//				}
//
//				if ($node instanceof BooleanAnd && $subNodeName === 'right') {
//					$scope = $scope->filterByTruthyValue($node->left);
//				}
//				if ($node instanceof BooleanOr && $subNodeName === 'right') {
//					$scope = $scope->filterByFalseyValue($node->left);
//				}
//
//				if (($node instanceof Assign || $node instanceof AssignRef) && $subNodeName === 'var') {
//					$scope = $this->lookForEnterVariableAssign($scope, $node->var);
//				}
//
//				if ($node instanceof BinaryOp && $subNodeName === 'right') {
//					$scope = $this->lookForAssigns($scope, $node->left);
//				}
//
//				if ($node instanceof Expr\Empty_ && $subNodeName === 'expr') {
//					$scope = $this->specifyProperty($scope, $node->expr);
//					$scope = $this->lookForEnterVariableAssign($scope, $node->expr);
//				}
//
//				if (
//					$node instanceof ArrayItem
//					&& $subNodeName === 'value'
//					&& $node->key !== null
//				) {
//					$scope = $this->lookForAssigns($scope, $node->key);
//				}
//
//				$nodeScope = $scope->exitFirstLevelStatements();
//				if ($scope->isInFirstLevelStatement()) {
//					if ($node instanceof Ternary && $subNodeName !== 'cond') {
//						$nodeScope = $scope->enterFirstLevelStatements();
//					} elseif (
//						($node instanceof BooleanAnd || $node instanceof BinaryOp\BooleanOr)
//						&& $subNodeName === 'right'
//					) {
//						$nodeScope = $scope->enterFirstLevelStatements();
//					}
//				}
//
//				if ($node instanceof MethodCall && $subNodeName === 'var' && isset($closureCallScope)) {
//					$nodeScope = $closureCallScope->exitFirstLevelStatements();
//				}
//
//				$this->processNode($subNode, $nodeScope, $nodeCallback);
//			}
//		}
//	}
//
//	private function lookForEnterVariableAssign(Scope $scope, Node $node): Scope
//	{
//		if ($node instanceof Variable && is_string($node->name)) {
//			$scope = $scope->enterVariableAssign($node->name);
//		} elseif ($node instanceof ArrayDimFetch) {
//			while ($node instanceof ArrayDimFetch) {
//				$node = $node->var;
//			}
//
//			if ($node instanceof Variable && is_string($node->name)) {
//				$scope = $scope->enterVariableAssign($node->name);
//			}
//		} elseif ($node instanceof List_ || $node instanceof Array_) {
//			$listItems = isset($node->items) ? $node->items : $node->vars;
//			foreach ($listItems as $listItem) {
//				if ($listItem === null) {
//					continue;
//				}
//				$listItemValue = $listItem;
//				if ($listItemValue instanceof Expr\ArrayItem) {
//					$listItemValue = $listItemValue->value;
//				}
//				$scope = $this->lookForEnterVariableAssign($scope, $listItemValue);
//			}
//		}
//
//		return $scope;
//	}
//
//	private function lookForAssigns(Scope $scope, \PhpParser\Node $node): Scope
//	{
//		if ($node instanceof StaticVar) {
//			$scope = $scope->assignVariable($node->name, $node->default !== null ? $scope->getType($node->default) : null);
//		} elseif ($node instanceof Static_) {
//			foreach ($node->vars as $var) {
//				$scope = $this->lookForAssigns($scope, $var);
//			}
//		} elseif ($node instanceof If_) {
//			$scope = $this->lookForAssigns($scope, $node->cond);
//			$ifStatement = new StatementList(
//				$scope->filterByTruthyValue($node->cond),
//				array_merge([$node->cond], $node->stmts),
//				false
//			);
//
//			$elseIfScope = $scope->filterByFalseyValue($node->cond);
//			$elseIfStatements = [];
//			foreach ($node->elseifs as $elseIf) {
//				$elseIfStatements[] = new StatementList($elseIfScope, array_merge([$elseIf->cond], $elseIf->stmts), false);
//				$elseIfScope = $elseIfScope->filterByFalseyValue($elseIf->cond);
//			}
//
//			$statements = [
//				$ifStatement,
//				new StatementList($elseIfScope, $node->else !== null ? $node->else->stmts : [], true),
//			];
//			$statements = array_merge($statements, $elseIfStatements);
//
//			$scope = $this->lookForAssignsInBranches($scope, $statements);
//		} elseif ($node instanceof TryCatch) {
//			$statements = [
//				new StatementList($scope, $node->stmts, true),
//			];
//			foreach ($node->catches as $catch) {
//				if (isset($catch->types)) {
//					$catchTypes = $catch->types;
//				} elseif (isset($catch->type)) {
//					$catchTypes = [$catch->type];
//				} else {
//					throw new \PHPStan\ShouldNotHappenException();
//				}
//				$statements[] = new StatementList($scope->enterCatch(
//					$catchTypes,
//					$catch->var
//				), $catch->stmts, true);
//			}
//
//			$scope = $this->lookForAssignsInBranches($scope, $statements);
//			if (isset($node->finallyStmts)) {
//				foreach ($node->finallyStmts as $statement) {
//					$scope = $this->lookForAssigns($scope, $statement);
//				}
//			} elseif (isset($node->finally)) {
//				foreach ($node->finally->stmts as $statement) {
//					$scope = $this->lookForAssigns($scope, $statement);
//				}
//			}
//		} elseif ($node instanceof MethodCall || $node instanceof FuncCall || $node instanceof Expr\StaticCall) {
//			if ($node instanceof MethodCall) {
//				$scope = $this->lookForAssigns($scope, $node->var);
//			}
//			foreach ($node->args as $argument) {
//				$scope = $this->lookForAssigns($scope, $argument);
//			}
//
//			$parametersAcceptor = $this->findParametersAcceptorInFunctionCall($node, $scope);
//
//			if ($parametersAcceptor !== null) {
//				$parameters = $parametersAcceptor->getParameters();
//				foreach ($node->args as $i => $arg) {
//					$assignByReference = false;
//					if (isset($parameters[$i])) {
//						$assignByReference = $parameters[$i]->isPassedByReference();
//					} elseif (count($parameters) > 0 && $parametersAcceptor->isVariadic()) {
//						$lastParameter = $parameters[count($parameters) - 1];
//						$assignByReference = $lastParameter->isPassedByReference();
//					}
//
//					if (!$assignByReference) {
//						continue;
//					}
//
//					$arg = $node->args[$i]->value;
//					if ($arg instanceof Variable && is_string($arg->name)) {
//						$scope = $scope->assignVariable($arg->name, new MixedType());
//					}
//				}
//			}
//			if (
//				$node instanceof FuncCall
//				&& $node->name instanceof Name
//				&& in_array((string) $node->name, [
//					'fopen',
//					'file_get_contents',
//				], true)
//			) {
//				$scope = $scope->assignVariable('http_response_header', new ArrayType(new StringType(), false));
//			}
//		} elseif ($node instanceof BinaryOp) {
//			$scope = $this->lookForAssigns($scope, $node->left);
//			$scope = $this->lookForAssigns($scope, $node->right);
//		} elseif ($node instanceof Arg) {
//			$scope = $this->lookForAssigns($scope, $node->value);
//		} elseif ($node instanceof BooleanNot) {
//			$scope = $this->lookForAssigns($scope, $node->expr);
//		} elseif ($node instanceof Ternary) {
//			$scope = $this->lookForAssigns($scope, $node->cond);
//		} elseif ($node instanceof Array_) {
//			foreach ($node->items as $item) {
//				if ($item->key !== null) {
//					$scope = $this->lookForAssigns($scope, $item->key);
//				}
//				$scope = $this->lookForAssigns($scope, $item->value);
//			}
//		} elseif ($node instanceof New_) {
//			foreach ($node->args as $arg) {
//				$scope = $this->lookForAssigns($scope, $arg);
//			}
//		} elseif ($node instanceof Do_) {
//			foreach ($node->stmts as $statement) {
//				$scope = $this->lookForAssigns($scope, $statement);
//			}
//		} elseif ($node instanceof Switch_) {
//			$statements = [];
//			$hasDefault = false;
//			foreach ($node->cases as $case) {
//				if ($case->cond === null) {
//					$hasDefault = true;
//				}
//				$statements[] = new StatementList($scope, $case->stmts, true);
//			}
//
//			if (!$hasDefault) {
//				$statements[] = new StatementList($scope, [], true);
//			}
//
//			$scope = $this->lookForAssignsInBranches($scope, $statements, true);
//		} elseif ($node instanceof Cast) {
//			$scope = $this->lookForAssigns($scope, $node->expr);
//		} elseif ($node instanceof For_) {
//			if ($this->polluteScopeWithLoopInitialAssignments) {
//				foreach ($node->init as $initExpr) {
//					$scope = $this->lookForAssigns($scope, $initExpr);
//				}
//
//				foreach ($node->cond as $condExpr) {
//					$scope = $this->lookForAssigns($scope, $condExpr);
//				}
//			}
//
//			$statements = [
//				new StatementList($scope, $node->stmts, true),
//				new StatementList($scope, [], true), // in order not to add variables existing only inside the for loop
//			];
//			$scope = $this->lookForAssignsInBranches($scope, $statements);
//		} elseif ($node instanceof While_) {
//			if ($this->polluteScopeWithLoopInitialAssignments) {
//				$scope = $this->lookForAssigns($scope, $node->cond);
//			}
//
//			$statements = [
//				new StatementList($scope, $node->stmts, true),
//				new StatementList($scope, [], true), // in order not to add variables existing only inside the for loop
//			];
//			$scope = $this->lookForAssignsInBranches($scope, $statements);
//		} elseif ($node instanceof ErrorSuppress) {
//			$scope = $this->lookForAssigns($scope, $node->expr);
//		} elseif ($node instanceof \PhpParser\Node\Stmt\Unset_) {
//			foreach ($node->vars as $var) {
//				if ($var instanceof Variable && is_string($var->name)) {
//					$scope = $scope->unsetVariable($var->name);
//				}
//			}
//		} elseif ($node instanceof Echo_) {
//			foreach ($node->exprs as $echoedExpr) {
//				$scope = $this->lookForAssigns($scope, $echoedExpr);
//			}
//		} elseif ($node instanceof Print_) {
//			$scope = $this->lookForAssigns($scope, $node->expr);
//		} elseif ($node instanceof Foreach_) {
//			$scope = $this->lookForAssigns($scope, $node->expr);
//			$scope = $this->enterForeach($scope, $node);
//			$statements = [
//				new StatementList($scope, $node->stmts, true),
//				new StatementList($scope, [], true), // in order not to add variables existing only inside the for loop
//			];
//			$scope = $this->lookForAssignsInBranches($scope, $statements);
//		} elseif ($node instanceof Isset_) {
//			foreach ($node->vars as $var) {
//				$scope = $this->lookForAssigns($scope, $var);
//			}
//		} elseif ($node instanceof Expr\Empty_) {
//			$scope = $this->lookForAssigns($scope, $node->expr);
//		} elseif ($node instanceof ArrayDimFetch && $node->dim !== null) {
//			$scope = $this->lookForAssigns($scope, $node->dim);
//		} elseif ($node instanceof Expr\Closure) {
//			foreach ($node->uses as $closureUse) {
//				if (!$closureUse->byRef || $scope->hasVariableType($closureUse->var)) {
//					continue;
//				}
//
//				$scope = $scope->assignVariable($closureUse->var, new MixedType());
//			}
//		} elseif ($node instanceof Instanceof_) {
//			$scope = $this->lookForAssigns($scope, $node->expr);
//		}
//
//		$scope = $this->updateScopeForVariableAssign($scope, $node);
//
//		return $scope;
//	}
//
//	private function updateScopeForVariableAssign(Scope $scope, \PhpParser\Node $node): Scope
//	{
//		if ($node instanceof Assign || $node instanceof AssignRef || $node instanceof Isset_ || $node instanceof Expr\AssignOp) {
//			if ($node instanceof Assign || $node instanceof AssignRef || $node instanceof Expr\AssignOp) {
//				$vars = [$node->var];
//			} elseif ($node instanceof Isset_) {
//				$vars = $node->vars;
//			} else {
//				throw new \PHPStan\ShouldNotHappenException();
//			}
//
//			foreach ($vars as $var) {
//				$type = null;
//				if ($node instanceof Assign || $node instanceof AssignRef) {
//					$type = $scope->getType($node->expr);
//				} elseif ($node instanceof Expr\AssignOp) {
//					$type = $scope->getType($node);
//				}
//				$scope = $this->assignVariable($scope, $var, $type);
//			}
//
//			if ($node instanceof Assign || $node instanceof AssignRef) {
//				if ($node->var instanceof Array_ || $node->var instanceof List_) {
//					$scope = $this->lookForArrayDestructuringArray($scope, $node->var);
//				}
//				$scope = $this->lookForAssigns($scope, $node->expr);
//				$comment = CommentHelper::getDocComment($node);
//				if ($comment !== null && $node->var instanceof Variable && is_string($node->var->name)) {
//					$variableName = $node->var->name;
//					$processVarAnnotation = function (string $matchedType, string $matchedVariableName) use ($scope, $variableName): Scope {
//						$fileTypeMap = $this->fileTypeMapper->getTypeMap($scope->getFile());
//						if (isset($fileTypeMap[$matchedType]) && $matchedVariableName === $variableName) {
//							return $scope->assignVariable($matchedVariableName, $fileTypeMap[$matchedType]);
//						}
//
//						return $scope;
//					};
//
//					if (preg_match('#@var\s+' . FileTypeMapper::TYPE_PATTERN . '\s+\$([a-zA-Z0-9_]+)#', $comment, $matches)) {
//						$scope = $processVarAnnotation($matches[1], $matches[2]);
//					} elseif (preg_match('#@var\s+\$([a-zA-Z0-9_]+)\s+' . FileTypeMapper::TYPE_PATTERN . '#', $comment, $matches)) {
//						$scope = $processVarAnnotation($matches[2], $matches[1]);
//					}
//				}
//			}
//		}
//
//		return $scope;
//	}
//
//	private function assignVariable(Scope $scope, Node $var, Type $subNodeType = null): Scope
//	{
//		if ($var instanceof Variable && is_string($var->name)) {
//			$scope = $scope->assignVariable($var->name, $subNodeType);
//		} elseif ($var instanceof ArrayDimFetch) {
//			$depth = 0;
//			while ($var instanceof ArrayDimFetch) {
//				$var = $var->var;
//				$depth++;
//			}
//
//			if (isset($var->dim)) {
//				$scope = $this->lookForAssigns($scope, $var->dim);
//			}
//
//			if ($var instanceof Variable && is_string($var->name)) {
//				if ($scope->hasVariableType($var->name)) {
//					$arrayDimFetchVariableType = $scope->getVariableType($var->name);
//					if (
//						!$arrayDimFetchVariableType instanceof ArrayType
//						&& !$arrayDimFetchVariableType instanceof MixedType
//					) {
//						return $scope;
//					}
//				}
//				$arrayType = ArrayType::createDeepArrayType(
//					new NestedArrayItemType($subNodeType !== null ? $subNodeType : new MixedType(), $depth),
//					false
//				);
//				if ($scope->hasVariableType($var->name)) {
//					$arrayType = $scope->getVariableType($var->name)->combineWith($arrayType);
//				}
//
//				$scope = $scope->assignVariable($var->name, $arrayType);
//			}
//		} elseif ($var instanceof PropertyFetch && $subNodeType !== null) {
//			$scope = $scope->specifyExpressionType($var, $subNodeType);
//		} elseif ($var instanceof Expr\StaticPropertyFetch && $subNodeType !== null) {
//			$scope = $scope->specifyExpressionType($var, $subNodeType);
//		} else {
//			$scope = $this->lookForAssigns($scope, $var);
//		}
//
//		return $scope;
//	}
//
//	/**
//	 * @param \PHPStan\Analyser\Scope $initialScope
//	 * @param \PHPStan\Analyser\StatementList[] $statementsLists
//	 * @param bool $isSwitchCase
//	 * @return Scope
//	 */
//	private function lookForAssignsInBranches(Scope $initialScope, array $statementsLists, bool $isSwitchCase = false): Scope
//	{
//		/** @var \PHPStan\Analyser\Scope|null $intersectedScope */
//		$intersectedScope = null;
//
//		/** @var \PHPStan\Analyser\Scope|null $previousBranchScope */
//		$previousBranchScope = null;
//
//		$allBranchesScope = $initialScope;
//
//		$removeKeysFromScope = null;
//		foreach ($statementsLists as $i => $statementList) {
//			$statements = $statementList->getStatements();
//			$branchScope = $statementList->getScope();
//
//			$earlyTerminationStatement = null;
//			foreach ($statements as $j => $statement) {
//				$branchScope = $this->lookForAssigns($branchScope, $statement);
//				if (!$statementList->shouldCarryOverSpecificTypes() && $j === 0) {
//					$removeKeysFromScope = $branchScope;
//				}
//				$earlyTerminationStatement = $this->findStatementEarlyTermination($statement, $branchScope);
//				if ($earlyTerminationStatement !== null) {
//					if (!$isSwitchCase) {
//						$allBranchesScope = $allBranchesScope->addVariables($branchScope);
//						continue 2;
//					}
//					break;
//				}
//			}
//
//			$allBranchesScope = $allBranchesScope->addVariables($branchScope, !$isSwitchCase);
//
//			if ($earlyTerminationStatement === null || $earlyTerminationStatement instanceof Break_) {
//				if ($intersectedScope === null) {
//					$intersectedScope = $initialScope->addVariables($branchScope, true);
//				}
//
//				if ($isSwitchCase && $previousBranchScope !== null) {
//					$intersectedScope = $branchScope->addVariables($previousBranchScope);
//				} else {
//					$intersectedScope = $branchScope->intersectVariables($intersectedScope, true);
//				}
//			}
//
//			if ($earlyTerminationStatement === null) {
//				$previousBranchScope = $branchScope;
//			} else {
//				$previousBranchScope = null;
//			}
//		}
//
//		if ($intersectedScope !== null) {
//			$combine = isset($earlyTerminationStatement) && $earlyTerminationStatement === null;
//			$scope = $intersectedScope->addVariables(
//				$allBranchesScope->intersectVariables($initialScope, $combine),
//				$combine
//			);
//			if ($removeKeysFromScope !== null) {
//				$scope = $scope->removeDifferenceInSpecificTypes($removeKeysFromScope, $initialScope);
//			}
//
//			return $scope->addSpecificTypesFromScope($initialScope);
//		}
//
//		return $initialScope;
//	}
//
//	/**
//	 * @param \PhpParser\Node[] $statements
//	 * @param \PHPStan\Analyser\Scope $scope
//	 * @return \PhpParser\Node|null
//	 */
//	private function findEarlyTermination(array $statements, Scope $scope)
//	{
//		foreach ($statements as $statement) {
//			$statement = $this->findStatementEarlyTermination($statement, $scope);
//			if ($statement !== null) {
//				return $statement;
//			}
//		}
//
//		return null;
//	}
//
//	/**
//	 * @param \PhpParser\Node $statement
//	 * @param \PHPStan\Analyser\Scope $scope
//	 * @return \PhpParser\Node|null
//	 */
//	private function findStatementEarlyTermination(Node $statement, Scope $scope)
//	{
//		if (
//			$statement instanceof Throw_
//			|| $statement instanceof Return_
//			|| $statement instanceof Continue_
//			|| $statement instanceof Break_
//			|| $statement instanceof Exit_
//		) {
//			return $statement;
//		} elseif ($statement instanceof MethodCall && count($this->earlyTerminatingMethodCalls) > 0) {
//			if (!is_string($statement->name)) {
//				return null;
//			}
//
//			$methodCalledOnType = $scope->getType($statement->var);
//			if ($methodCalledOnType->getClass() === null) {
//				return null;
//			}
//
//			if (!$this->broker->hasClass($methodCalledOnType->getClass())) {
//				return null;
//			}
//
//			$classReflection = $this->broker->getClass($methodCalledOnType->getClass());
//			foreach (array_merge([$methodCalledOnType->getClass()], $classReflection->getParentClassesNames()) as $className) {
//				if (!isset($this->earlyTerminatingMethodCalls[$className])) {
//					continue;
//				}
//
//				if (in_array($statement->name, $this->earlyTerminatingMethodCalls[$className], true)) {
//					return $statement;
//				}
//			}
//
//			return null;
//		} elseif ($statement instanceof If_) {
//			if ($statement->else === null) {
//				return null;
//			}
//
//			if (!$this->findEarlyTermination($statement->stmts, $scope)) {
//				return null;
//			}
//
//			foreach ($statement->elseifs as $elseIfStatement) {
//				if (!$this->findEarlyTermination($elseIfStatement->stmts, $scope)) {
//					return null;
//				}
//			}
//
//			if (!$this->findEarlyTermination($statement->else->stmts, $scope)) {
//				return null;
//			}
//
//			return $statement;
//		}
//
//		return null;
//	}
//
//	/**
//	 * @param \PhpParser\Node\Expr $functionCall
//	 * @param \PHPStan\Analyser\Scope $scope
//	 * @return null|\PHPStan\Reflection\ParametersAcceptor
//	 */
//	private function findParametersAcceptorInFunctionCall(Expr $functionCall, Scope $scope)
//	{
//		if ($functionCall instanceof FuncCall && $functionCall->name instanceof Name) {
//			if ($this->broker->hasFunction($functionCall->name, $scope)) {
//				return $this->broker->getFunction($functionCall->name, $scope);
//			}
//		} elseif ($functionCall instanceof MethodCall && is_string($functionCall->name)) {
//			$type = $scope->getType($functionCall->var);
//			if ($type->getClass() !== null && $this->broker->hasClass($type->getClass())) {
//				$classReflection = $this->broker->getClass($type->getClass());
//				$methodName = $functionCall->name;
//				if ($classReflection->hasMethod($methodName)) {
//					return $classReflection->getMethod($methodName, $scope);
//				}
//			}
//		} elseif (
//			$functionCall instanceof Expr\StaticCall
//			&& $functionCall->class instanceof Name
//			&& is_string($functionCall->name)) {
//			$className = (string) $functionCall->class;
//			if ($this->broker->hasClass($className)) {
//				$classReflection = $this->broker->getClass($className);
//				if ($classReflection->hasMethod($functionCall->name)) {
//					return $classReflection->getMethod($functionCall->name, $scope);
//				}
//			}
//		}
//
//		return null;
//	}
//
//	private function processTraitUse(Node\Stmt\TraitUse $node, Scope $classScope, \Closure $nodeCallback)
//	{
//		foreach ($node->traits as $trait) {
//			$traitName = (string) $trait;
//			if (!$this->broker->hasClass($traitName)) {
//				continue;
//			}
//			$traitReflection = $this->broker->getClass($traitName);
//			$fileName = $this->fileHelper->normalizePath($traitReflection->getNativeReflection()->getFileName());
//			if ($this->fileExcluder->isExcludedFromAnalysing($fileName)) {
//				return;
//			}
//			if (!isset($this->analysedFiles[$fileName])) {
//				return;
//			}
//			$parserNodes = $this->parser->parseFile($fileName);
//			$className = sprintf('class %s', $classScope->getClassReflection()->getDisplayName());
//			if ($classScope->getClassReflection()->getNativeReflection()->isAnonymous()) {
//				$className = 'anonymous class';
//			}
//			$classScope = $classScope->changeAnalysedContextFile(
//				sprintf(
//					'%s (in context of %s)',
//					$fileName,
//					$className
//				)
//			);
//
//			$this->processNodesForTraitUse($parserNodes, $traitName, $classScope, $nodeCallback);
//		}
//	}
//
//	/**
//	 * @param \PhpParser\Node[]|\PhpParser\Node $node
//	 * @param string $traitName
//	 * @param \PHPStan\Analyser\Scope $classScope
//	 * @param \Closure $nodeCallback
//	 */
//	private function processNodesForTraitUse($node, string $traitName, Scope $classScope, \Closure $nodeCallback)
//	{
//		if ($node instanceof Node) {
//			if ($node instanceof Node\Stmt\Trait_ && $traitName === (string) $node->namespacedName) {
//				$this->processNodes($node->stmts, $classScope->enterFirstLevelStatements(), $nodeCallback);
//				return;
//			}
//			if ($node instanceof Node\Stmt\ClassLike) {
//				return;
//			}
//			foreach ($node->getSubNodeNames() as $subNodeName) {
//				$subNode = $node->{$subNodeName};
//				$this->processNodesForTraitUse($subNode, $traitName, $classScope, $nodeCallback);
//			}
//		} elseif (is_array($node)) {
//			foreach ($node as $subNode) {
//				$this->processNodesForTraitUse($subNode, $traitName, $classScope, $nodeCallback);
//			}
//		}
//	}
//
//	private function enterClassMethod(Scope $scope, Node\Stmt\ClassMethod $classMethod): Scope
//	{
//		list($phpDocParameterTypes, $phpDocReturnType) = $this->getPhpDocs($scope, $classMethod);
//
//		return $scope->enterClassMethod(
//			$classMethod,
//			$phpDocParameterTypes,
//			$phpDocReturnType
//		);
//	}
//
	private function getPhpDocs(Scope $scope, Node\FunctionLike $functionLike): array
	{
		$phpDocParameterTypes = [];
		$phpDocReturnType = null;
		if ($functionLike->getDocComment() !== null) {
			$docComment = $functionLike->getDocComment()->getText();
			$file = $scope->getFile();
			if ($functionLike instanceof ClassMethod) {
				$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
					$this->broker,
					$docComment,
					$scope->getClassReflection()->getName(),
					$functionLike->name,
					$file
				);
				$docComment = $phpDocBlock->getDocComment();
				$file = $phpDocBlock->getFile();
			}
			$fileTypeMap = $this->fileTypeMapper->getTypeMap($file);
			$phpDocParameterTypes = TypehintHelper::getParameterTypesFromPhpDoc(
				$fileTypeMap,
				array_map(function (Param $parameter): string {
					return $parameter->name;
				}, $functionLike->getParams()),
				$docComment
			);
			$phpDocReturnType = TypehintHelper::getReturnTypeFromPhpDoc($fileTypeMap, $docComment);
		}

		return [$phpDocParameterTypes, $phpDocReturnType];
	}

	/**
	 * @param  Assign|AssignRef $node
	 * @param  Scope            $scope
	 * @param  Type|null        $extractedType
	 * @return bool
	 */
	private function tryExtractVariableTypeFromPhpDoc(Node $node, Scope $scope, Type &$extractedType = null): bool
	{
		$comment = CommentHelper::getDocComment($node);
		if ($comment === null) {
			return false;
		}

		if (preg_match('#@var\s+' . FileTypeMapper::TYPE_PATTERN . '\s+\$([a-zA-Z0-9_]+)#', $comment, $matches)) {
			list(, $typeDescription, $varName) = $matches;

		} elseif (preg_match('#@var\s+\$([a-zA-Z0-9_]+)\s+' . FileTypeMapper::TYPE_PATTERN . '#', $comment, $matches)) {
			list(, $varName, $typeDescription) = $matches;

		} else {
			return false;
		}

		if (!$node->var instanceof Variable || $varName !== $node->var->name) {
			return false;
		}

		$fileTypeMap = $this->fileTypeMapper->getTypeMap($scope->getFile());
		if (!isset($fileTypeMap[$typeDescription])) {
			return false;
		}

		$extractedType = $fileTypeMap[$typeDescription];
		return true;
	}


	private function processTruthyFilteringExpr(Expr $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$backup = $this->scopeFilteringMode;
		$this->scopeFilteringMode = true;

		$scope = $this->processExprNode($node, $scope, $nodeCallback, $resultType);

		$this->scopeFilteringMode = $backup;

		return $scope;
	}


	private function processFalseyFilteringExpr(Expr $node, Scope $scope, callable $nodeCallback, Type &$resultType = NULL): Scope
	{
		$backup = $this->scopeFilteringMode;
		$this->scopeFilteringMode = false;

		$scope = $this->processExprNode($node, $scope, $nodeCallback, $resultType);

		$this->scopeFilteringMode = $backup;

		return $scope;
	}

	public function filterScope(Expr $node, Scope $scope, Type $type): Scope
	{
		if ($this->scopeFilteringMode === true) {
			$scope = $scope->specifyExpressionType($node, $type);

		} elseif ($this->scopeFilteringMode === false) {
			$scope = $scope->removeTypeFromExpression($node, $type);

		} else {
			throw new \LogicException();
		}

		return $scope;
	}

//
//	private function enterFunction(Scope $scope, Node\Stmt\Function_ $function): Scope
//	{
//		list($phpDocParameterTypes, $phpDocReturnType) = $this->getPhpDocs($scope, $function);
//
//		return $scope->enterFunction(
//			$function,
//			$phpDocParameterTypes,
//			$phpDocReturnType
//		);
//	}

}
