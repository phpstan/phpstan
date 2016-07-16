<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
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
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
use PHPStan\Broker\Broker;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;

class NodeScopeResolver
{

	const SPECIAL_FUNCTIONS = [
		'preg_match' => [3],
		'preg_match_all' => [3],
		'preg_replace_callback' => [5],
		'preg_replace_callback_array' => [4],
		'preg_replace' => [5],
		'proc_open' => [3],
		'passthru' => [2],
		'parse_str' => [2],
		'exec' => [2, 3],
		'stream_socket_client' => [2, 3],
		'openssl_sign' => [2],
	];

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var bool */
	private $polluteScopeWithForLoopInitialAssignments;

	/** @var bool */
	private $polluteCatchScopeWithTryAssignments;

	/** @var bool */
	private $defineVariablesWithoutDefaultBranch;

	/** @var \PHPStan\Reflection\ClassReflection|null */
	private $anonymousClassReflection;

	public function __construct(
		Broker $broker,
		\PhpParser\PrettyPrinter\Standard $printer,
		bool $polluteScopeWithForLoopInitialAssignments,
		bool $polluteCatchScopeWithTryAssignments,
		bool $defineVariablesWithoutDefaultBranch
	)
	{
		$this->broker = $broker;
		$this->printer = $printer;
		$this->polluteScopeWithForLoopInitialAssignments = $polluteScopeWithForLoopInitialAssignments;
		$this->polluteCatchScopeWithTryAssignments = $polluteCatchScopeWithTryAssignments;
		$this->defineVariablesWithoutDefaultBranch = $defineVariablesWithoutDefaultBranch;
	}

	/**
	 * @param \PhpParser\Node[] $nodes
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure $nodeCallback
	 */
	public function processNodes(array $nodes, Scope $scope, \Closure $nodeCallback)
	{
		foreach ($nodes as $i => $node) {
			if (!($node instanceof \PhpParser\Node)) {
				continue;
			}

			if (
				$scope->getInFunctionCallName() !== null
				&& in_array($scope->getInFunctionCallName(), array_keys(self::SPECIAL_FUNCTIONS), true)
				&& $node instanceof Arg
			) {
				$functionName = $scope->getInFunctionCallName();
				$specialArgsPositions = self::SPECIAL_FUNCTIONS[$functionName];
				$value = $node->value;
				if (in_array($i + 1, $specialArgsPositions, true) && $value instanceof Variable) {
					$functionReflection = $this->broker->getFunction($functionName);
					$parameters = $functionReflection->getParameters();
					$scope = $scope->assignVariable($value->name, $parameters[$i]->getType());
				}
			}

			$this->processNode($node, $scope, $nodeCallback);
			$scope = $this->lookForAssigns($scope, $node);

			if ($node instanceof If_ && $node->cond instanceof BooleanNot) {
				if ($this->hasEarlyTermination($node->stmts)) {
					$negatedCondition = $node->cond->expr;
					$scope = $this->lookForInstanceOfs($scope, $negatedCondition);
				}
			} elseif ($node instanceof Node\Stmt\Declare_) {
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
			}
		}
	}

	private function processNode(\PhpParser\Node $node, Scope $scope, \Closure $nodeCallback)
	{
		$nodeCallback($node, $scope);

		if (
			$node instanceof \PhpParser\Node\Stmt\ClassLike
		) {
			if (isset($node->namespacedName)) {
				$scope = $scope->enterClass((string) $node->namespacedName);
			} else {
				$scope = $scope->enterAnonymousClass($this->anonymousClassReflection);
			}
		} elseif ($node instanceof \PhpParser\Node\Stmt\Function_) {
			$scope = $scope->enterFunction(
				$this->broker->getFunction((string) $node->namespacedName)
			);
		} elseif ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
			if ($scope->getClass() !== null) {
				$classReflection = $this->broker->getClass($scope->getClass());
			} else {
				$classReflection = $scope->getAnonymousClass();
			}

			$scope = $scope->enterFunction(
				$classReflection->getMethod($node->name)
			);
		} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
			$scope = $scope->enterNamespace((string) $node->name);
		} elseif (
			$node instanceof \PhpParser\Node\Expr\StaticCall
			&& (is_string($node->class) || $node->class instanceof \PhpParser\Node\Name)
			&& is_string($node->name)
			&& (string) $node->class === 'Closure'
			&& $node->name === 'bind'
		) {
			$scope = $scope->enterClosureBind();
		} elseif ($node instanceof \PhpParser\Node\Expr\Closure) {
			$scope = $scope->enterAnonymousFunction($node->params, $node->uses);
		} elseif ($node instanceof Foreach_) {
			// todo typy podle obsahu ArrayType
			if ($node->valueVar instanceof Variable) {
				$scope = $scope->enterForeach(
					$node->valueVar->name,
					$node->keyVar !== null && $node->keyVar instanceof Variable ? $node->keyVar->name : null
				);
			} else {
				if ($node->keyVar !== null && $node->keyVar instanceof Variable) {
					$scope = $scope->assignVariable($node->keyVar->name);
				}

				$scope = $this->lookForAssigns($scope, $node->valueVar);
			}
		} elseif ($node instanceof Catch_) {
			$scope = $scope->enterCatch(
				(string) $node->type,
				$node->var
			);
		} elseif ($node instanceof For_) {
			foreach ($node->init as $initExpr) {
				$scope = $this->lookForAssigns($scope, $initExpr);
			}

			foreach ($node->cond as $condExpr) {
				$scope = $this->lookForAssigns($scope, $condExpr);
			}

			foreach ($node->loop as $loopExpr) {
				$scope = $this->lookForAssigns($scope, $loopExpr);
			}
		} elseif ($node instanceof If_) {
			$scope = $this->lookForAssigns($scope, $node->cond);
		} elseif ($node instanceof ElseIf_) {
			$scope = $this->lookForAssigns($scope, $node->cond);
			$scope = $this->lookForInstanceOfs($scope, $node->cond);
		} elseif ($node instanceof While_) {
			$scope = $this->lookForAssigns($scope, $node->cond);
			$scope = $this->lookForInstanceOfs($scope, $node->cond);
		} elseif ($this->polluteCatchScopeWithTryAssignments && $node instanceof TryCatch) {
			foreach ($node->stmts as $statement) {
				$scope = $this->lookForAssigns($scope, $statement);
			}
		} elseif ($node instanceof Ternary) {
			$scope = $this->lookForAssigns($scope, $node->cond);
		} elseif ($node instanceof Do_) {
			foreach ($node->stmts as $statement) {
				$scope = $this->lookForAssigns($scope, $statement);
			}
		} elseif ($node instanceof FuncCall && $node->name instanceof Name) {
			$scope = $scope->enterFunctionCall((string) $node->name);
		} elseif ($node instanceof Array_) {
			foreach ($node->items as $item) {
				$scope = $this->lookForAssigns($scope, $item->value);
			}
		} elseif ($node instanceof New_ && $node->class instanceof Class_) {
			$node->args = [];
			foreach ($node->class->stmts as $i => $statement) {
				if (
					$statement instanceof Node\Stmt\ClassMethod
					&& $statement->name === '__construct'
				) {
					unset($node->class->stmts[$i]);
					$node->class->stmts = array_values($node->class->stmts);
					break;
				}
			}

			$code = $this->printer->prettyPrint([$node]);
			$classReflection = new \ReflectionClass(eval(sprintf('return %s', $code)));
			$this->anonymousClassReflection = $this->broker->getClassFromReflection($classReflection);
		}

		$originalScope = $scope;
		foreach ($node->getSubNodeNames() as $subNodeName) {
			$scope = $originalScope;
			$subNode = $node->{$subNodeName};
			if (is_array($subNode)) {
				if ($node instanceof If_ && $subNodeName === 'stmts') {
					$scope = $this->lookForInstanceOfs($scope, $node->cond);
				}

				$this->processNodes($subNode, $scope, $nodeCallback);
			} elseif ($subNode instanceof \PhpParser\Node) {
				if ($node instanceof Coalesce && $subNodeName === 'left') {
					$scope = $this->assignVariable($scope, $subNode);
				}

				if ($node instanceof Ternary && $subNodeName === 'if') {
					$scope = $this->lookForInstanceOfs($scope, $node->cond);
				}

				if ($node instanceof BooleanAnd && $subNodeName === 'right') {
					$scope = $this->lookForInstanceOfs($scope, $node->left);
				}

				if ($node instanceof Assign && $subNodeName === 'var') {
					$var = $node->var;
					if ($var instanceof Variable && is_string($var->name)) {
						$scope = $scope->enterVariableAssign($var->name);
					} elseif ($var instanceof ArrayDimFetch) {
						while ($var instanceof ArrayDimFetch) {
							$var = $var->var;
						}

						if ($var instanceof Variable && is_string($var->name)) {
							$scope = $scope->enterVariableAssign($var->name);
						}
					} elseif ($var instanceof List_) {
						foreach ($var->vars as $var) {
							if ($var instanceof Variable && is_string($var->name)) {
								$scope = $scope->enterVariableAssign($var->name);
							}
						}
					}
				}

				$this->processNode($subNode, $scope, $nodeCallback);
			}
		}
	}

	private function lookForInstanceOfs(Scope $scope, Node $node): Scope
	{
		if ($node instanceof Instanceof_ && $node->class instanceof Name) {
			$class = (string) $node->class;
			if ($class === 'static') {
				return $scope;
			}

			if ($class === 'self' && $scope->getClass() !== null) {
				$class = $scope->getClass();
			}

			return $scope->specifyObjectType($node->expr, $class);
		} elseif ($node instanceof BooleanAnd) {
			$scope = $this->lookForInstanceOfs($scope, $node->left);
			$scope = $this->lookForInstanceOfs($scope, $node->right);
		} elseif ($node instanceof BooleanOr) {
			$scope = $this->lookForInstanceOfs($scope, $node->left);
			$scope = $this->lookForInstanceOfs($scope, $node->right);
		}

		return $scope;
	}

	private function lookForAssigns(Scope $scope, \PhpParser\Node $node): Scope
	{
		if ($node instanceof StaticVar) {
			$scope = $scope->assignVariable($node->name, $node->default !== null ? $scope->getType($node->default) : null);
		} elseif ($node instanceof Static_) {
			foreach ($node->vars as $var) {
				$scope = $this->lookForAssigns($scope, $var);
			}
		} elseif ($node instanceof If_) {
			$scope = $this->lookForAssigns($scope, $node->cond);
			$statements = [
				new StatementList($scope, array_merge([$node->cond], $node->stmts)),
				new StatementList($scope, $node->else !== null ? $node->else->stmts : ($this->defineVariablesWithoutDefaultBranch ? null : [])),
			];
			foreach ($node->elseifs as $elseIf) {
				$statements[] = new StatementList($scope, array_merge([$elseIf->cond], $elseIf->stmts));
			}

			$scope = $this->lookForAssignsInBranches($scope, $statements);
		} elseif ($node instanceof TryCatch) {
			$statements = [
				new StatementList($scope, $node->stmts),
				new StatementList($scope, $node->finallyStmts !== null ? $node->finallyStmts : null),
			];
			foreach ($node->catches as $catch) {
				$statements[] = new StatementList($scope->enterCatch(
					(string) $catch->type,
					$catch->var
				), $catch->stmts);
			}

			$scope = $this->lookForAssignsInBranches($scope, $statements);
		} elseif ($node instanceof MethodCall || $node instanceof FuncCall) {
			if ($node instanceof MethodCall) {
				$scope = $this->lookForAssigns($scope, $node->var);
			}
			foreach ($node->args as $argument) {
				$scope = $this->lookForAssigns($scope, $argument);
			}

			if ($node instanceof FuncCall && $node->name instanceof Name) {
				if (in_array((string) $node->name, array_keys(self::SPECIAL_FUNCTIONS), true)) {
					$functionName = (string) $node->name;
					$newVariablePositions = self::SPECIAL_FUNCTIONS[$functionName];
					foreach ($newVariablePositions as $newVariablePosition) {
						if (count($node->args) >= $newVariablePosition) {
							$arg = $node->args[$newVariablePosition - 1]->value;
							if ($arg instanceof Variable) {
								$functionReflection = $this->broker->getFunction($functionName);
								$parameters = $functionReflection->getParameters();
								$scope = $scope->assignVariable($arg->name, $parameters[$newVariablePosition - 1]->getType());
							}
						}
					}
				}
			}
		} elseif ($node instanceof BinaryOp) {
			$scope = $this->lookForAssigns($scope, $node->left);
			$scope = $this->lookForAssigns($scope, $node->right);
		} elseif ($node instanceof Arg) {
			$scope = $this->lookForAssigns($scope, $node->value);
		} elseif ($node instanceof BooleanNot) {
			$scope = $this->lookForAssigns($scope, $node->expr);
		} elseif ($node instanceof Ternary) {
			$scope = $this->lookForAssigns($scope, $node->else);
		} elseif ($node instanceof List_) {
			foreach ($node->vars as $var) {
				// todo typy podle obsahu pole
				if ($var instanceof Variable) {
					$scope = $scope->assignVariable($var->name);
				} elseif ($var instanceof ArrayDimFetch && $var->var instanceof Variable) {
					$scope = $scope->assignVariable($var->var->name);
				} elseif ($var !== null) {
					$scope = $this->lookForAssigns($scope, $var);
				}
			}
		} elseif ($node instanceof Array_) {
			foreach ($node->items as $item) {
				$scope = $this->lookForAssigns($scope, $item->value);
			}
		} elseif ($node instanceof New_) {
			foreach ($node->args as $arg) {
				$scope = $this->lookForAssigns($scope, $arg);
			}
		} elseif ($node instanceof Do_) {
			foreach ($node->stmts as $statement) {
				$scope = $this->lookForAssigns($scope, $statement);
			}
		} elseif ($node instanceof Switch_) {
			$statements = [];
			$hasDefault = false;
			foreach ($node->cases as $case) {
				if ($case->cond === null) {
					$hasDefault = true;
				}

				$statements[] = new StatementList($scope, $case->stmts);
			}

			if (!$hasDefault) {
				$statements = [];
			}

			$scope = $this->lookForAssignsInBranches($scope, $statements, true);
		} elseif ($node instanceof Cast) {
			$scope = $this->lookForAssigns($scope, $node->expr);
		} elseif ($this->polluteScopeWithForLoopInitialAssignments && $node instanceof For_) {
			foreach ($node->init as $initExpr) {
				$scope = $this->lookForAssigns($scope, $initExpr);
			}

			foreach ($node->cond as $condExpr) {
				$scope = $this->lookForAssigns($scope, $condExpr);
			}
		} elseif ($node instanceof ErrorSuppress) {
			$scope = $this->lookForAssigns($scope, $node->expr);
		} elseif ($node instanceof \PhpParser\Node\Stmt\Unset_) {
			foreach ($node->vars as $var) {
				if ($var instanceof Variable && is_string($var->name)) {
					$scope = $scope->unsetVariable($var->name);
				}
			}
		}

		$scope = $this->updateScopeForVariableAssign($scope, $node);

		return $scope;
	}

	private function updateScopeForVariableAssign(Scope $scope, \PhpParser\Node $node): Scope
	{
		if ($node instanceof Assign || $node instanceof Isset_) {
			if ($node instanceof Assign) {
				$vars = [$node->var];
			} elseif ($node instanceof Isset_) {
				$vars = $node->vars;
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}

			foreach ($vars as $var) {
				$scope = $this->assignVariable(
					$scope,
					$var,
					$node instanceof Assign ? $scope->getType($node->expr) : null
				);
			}

			if ($node instanceof Assign) {
				$scope = $this->lookForAssigns($scope, $node->expr);
			}
		}

		return $scope;
	}

	private function assignVariable(Scope $scope, Node $var, Type $subNodeType = null): Scope
	{
		if ($var instanceof Variable) {
			$scope = $scope->assignVariable($var->name, $subNodeType);
		} elseif ($var instanceof ArrayDimFetch) {
			while ($var instanceof ArrayDimFetch) {
				$var = $var->var;
			}

			if ($var instanceof Variable) {
				$scope = $scope->assignVariable(
					$var->name,
					new ArrayType(false)
				);
			}

			if (isset($var->dim)) {
				$scope = $this->lookForAssigns($scope, $var->dim);
			}
		} else {
			$scope = $this->lookForAssigns($scope, $var);
		}

		return $scope;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $initialScope
	 * @param \PHPStan\Analyser\StatementList[] $statementsLists
	 * @param bool $isSwitchCase
	 * @return Scope
	 */
	private function lookForAssignsInBranches(Scope $initialScope, array $statementsLists, bool $isSwitchCase = false): Scope
	{
		$intersectedScope = null;
		$previousBranchScope = null;
		foreach ($statementsLists as $i => $statementList) {
			$statements = $statementList->getStatements();
			$branchScope = $statementList->getScope();

			if ($statements === null) {
				continue;
			}

			$hasEarlyTermination = $this->hasEarlyTermination($statements);
			if ($hasEarlyTermination && !$isSwitchCase) {
				continue;
			}

			$mergeWithPrevious = $isSwitchCase;

			foreach ($statements as $statement) {
				$branchScope = $this->lookForAssigns($branchScope, $statement);
			}

			if ($intersectedScope === null) {
				$intersectedScope = $branchScope;
			} elseif ($mergeWithPrevious) {
				if ($previousBranchScope !== null) {
					$intersectedScope = $branchScope->addVariables($previousBranchScope);
				}
			} else {
				$intersectedScope = $branchScope->intersectVariables($intersectedScope);
			}

			$previousBranchScope = $branchScope;
		}

		return $intersectedScope !== null ? $intersectedScope : $initialScope;
	}

	/**
	 * @param \PhpParser\Node[] $statements
	 * @return bool
	 */
	private function hasEarlyTermination(array $statements): bool
	{
		foreach ($statements as $statement) {
			if (
				$statement instanceof Throw_
				|| $statement instanceof Return_
				|| $statement instanceof Continue_
				|| $statement instanceof Break_
				|| $statement instanceof Exit_
			) {
				return true;
			} elseif ($statement instanceof MethodCall) {
				// todo temporary - extension subject
				if (
					$statement->var instanceof Variable
					&& $statement->var->name === 'this'
					&& is_string($statement->name)
					&& in_array($statement->name, [
						'redirect',
						'redirectUrl',
						'sendPayload',
						'sendResponse',
						'sendJson',
						'terminate',
						'error',
						'raiseError',
					], true)
				) {
					return true;
				}
			}
		}

		return false;
	}

}
