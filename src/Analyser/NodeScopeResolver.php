<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
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
use PHPStan\Type\CommentHelper;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\NestedArrayItemType;
use PHPStan\Type\Type;

class NodeScopeResolver
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	/** @var bool */
	private $polluteScopeWithLoopInitialAssignments;

	/** @var bool */
	private $polluteCatchScopeWithTryAssignments;

	/** @var bool */
	private $defineVariablesWithoutDefaultBranch;

	/** @var string[][] className(string) => methods(string[]) */
	private $earlyTerminatingMethodCalls;

	/** @var \PHPStan\Reflection\ClassReflection|null */
	private $anonymousClassReflection;

	public function __construct(
		Broker $broker,
		\PhpParser\PrettyPrinter\Standard $printer,
		FileTypeMapper $fileTypeMapper,
		TypeSpecifier $typeSpecifier,
		bool $polluteScopeWithLoopInitialAssignments,
		bool $polluteCatchScopeWithTryAssignments,
		bool $defineVariablesWithoutDefaultBranch,
		array $earlyTerminatingMethodCalls
	)
	{
		$this->broker = $broker;
		$this->printer = $printer;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->typeSpecifier = $typeSpecifier;
		$this->polluteScopeWithLoopInitialAssignments = $polluteScopeWithLoopInitialAssignments;
		$this->polluteCatchScopeWithTryAssignments = $polluteCatchScopeWithTryAssignments;
		$this->defineVariablesWithoutDefaultBranch = $defineVariablesWithoutDefaultBranch;
		$this->earlyTerminatingMethodCalls = $earlyTerminatingMethodCalls;
	}

	/**
	 * @param \PhpParser\Node[] $nodes
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \Closure $nodeCallback
	 * @param \PHPStan\Analyser\Scope $closureBindScope
	 */
	public function processNodes(
		array $nodes,
		Scope $scope,
		\Closure $nodeCallback,
		Scope $closureBindScope = null
	)
	{
		foreach ($nodes as $i => $node) {
			if (!($node instanceof \PhpParser\Node)) {
				continue;
			}

			if ($scope->getInFunctionCall() !== null && $node instanceof Arg) {
				$functionCall = $scope->getInFunctionCall();
				$value = $node->value;

				$parameters = $this->findParametersInFunctionCall($functionCall, $scope);

				if (
					$parameters !== null
					&& isset($parameters[$i])
					&& $parameters[$i]->isPassedByReference()
					&& $value instanceof Variable
				) {
					$scope = $scope->assignVariable($value->name, new MixedType(true));
				}
			}

			$nodeScope = $scope;
			if ($i === 0 && $closureBindScope !== null) {
				$nodeScope = $closureBindScope;
			}

			$this->processNode($node, $nodeScope, $nodeCallback);
			$scope = $this->lookForAssigns($scope, $node);

			if ($node instanceof If_) {
				if ($this->hasEarlyTermination($node->stmts, $scope)) {
					$scope = $this->lookForTypeSpecificationsInEarlyTermination($scope, $node->cond);
					$this->processNode($node->cond, $scope, function (Node $node, Scope $inScope) use (&$scope) {
						if ($inScope->isNegated()) {
							if ($node instanceof Isset_) {
								foreach ($node->vars as $var) {
									if ($var instanceof PropertyFetch) {
										$scope = $scope->specifyFetchedPropertyFromIsset($var);
									}
								}
							}
						}
					});
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
			} elseif (
				$node instanceof FuncCall
				&& $node->name instanceof Name
				&& (string) $node->name === 'assert'
				&& isset($node->args[0])
			) {
				$scope = $this->lookForTypeSpecifications($scope, $node->args[0]->value);
			} elseif (
				$node instanceof Assign
				&& $node->var instanceof Array_
			) {
				$scope = $this->lookForArrayDestructuringArray($scope, $node->var);
			}
		}
	}

	private function lookForArrayDestructuringArray(Scope $scope, Node $node): Scope
	{
		if ($node instanceof Array_) {
			foreach ($node->items as $item) {
				$scope = $this->lookForArrayDestructuringArray($scope, $item->value);
			}
		} elseif ($node instanceof Variable && is_string($node->name)) {
			$scope = $scope->assignVariable($node->name);
		}

		return $scope;
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
				$this->broker->getFunction($node->namespacedName, $scope)
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
			$thisType = null;
			if (isset($node->args[1])) {
				$argValue = $node->args[1]->value;
				if ($argValue instanceof Expr\ConstFetch && ((string) $argValue->name === 'null')) {
					$thisType = null;
				} else {
					$thisType = $scope->getType($argValue);
				}
			}
			$scopeClass = 'static';
			if (isset($node->args[2])) {
				$argValue = $node->args[2]->value;
				$argValueType = $scope->getType($argValue);
				if ($argValueType->getClass() !== null) {
					$scopeClass = $argValueType->getClass();
				} elseif (
					$argValue instanceof Expr\ClassConstFetch
					&& $argValue->name === 'class'
					&& $argValue->class instanceof Name
				) {
					$resolvedName = $scope->resolveName($argValue->class);
					if ($resolvedName !== null) {
						$scopeClass = $resolvedName;
					}
				} elseif ($argValue instanceof Node\Scalar\String_) {
					$scopeClass = $argValue->value;
				}
			}
			$closureBindScope = $scope->enterClosureBind($thisType, $scopeClass);
		} elseif ($node instanceof \PhpParser\Node\Expr\Closure) {
			$scope = $scope->enterAnonymousFunction($node->params, $node->uses, $node->returnType);
		} elseif ($node instanceof Foreach_) {
			if ($node->valueVar instanceof Variable) {
				$scope = $scope->enterForeach(
					$node->expr,
					$node->valueVar->name,
					$node->keyVar !== null && $node->keyVar instanceof Variable ? $node->keyVar->name : null
				);
			} else {
				if ($node->keyVar !== null && $node->keyVar instanceof Variable) {
					$scope = $scope->assignVariable($node->keyVar->name);
				}

				if ($node->valueVar instanceof Array_) {
					$scope = $this->lookForArrayDestructuringArray($scope, $node->valueVar);
				} else {
					$scope = $this->lookForAssigns($scope, $node->valueVar);
				}
			}
		} elseif ($node instanceof Catch_) {
			$scope = $scope->enterCatch(
				$node->types,
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
			$ifScope = $scope;
			$scope = $this->lookForTypeSpecifications($scope, $node->cond);
			$this->processNode($node->cond, $scope, $nodeCallback);
			$this->processNodes($node->stmts, $scope, $nodeCallback);

			foreach ($node->elseifs as $elseif) {
				$this->processNode($elseif, $ifScope, $nodeCallback);
				$ifScope = $this->lookForAssigns($ifScope, $elseif->cond);
			}
			if ($node->else !== null) {
				$this->processNode($node->else, $ifScope, $nodeCallback);
			}

			return;
		} elseif ($node instanceof Switch_) {
			$scope = $this->lookForAssigns($scope, $node->cond);
			$switchScope = $scope;
			$switchConditionIsTrue = $node->cond instanceof Expr\ConstFetch && strtolower((string) $node->cond->name) === 'true';
			foreach ($node->cases as $caseNode) {
				if ($caseNode->cond !== null) {
					$switchScope = $this->lookForAssigns($switchScope, $caseNode->cond);

					if ($switchConditionIsTrue) {
						$switchScope = $this->lookForTypeSpecifications($switchScope, $caseNode->cond);
					}
				}
				$this->processNode($caseNode, $switchScope, $nodeCallback);
				if ($this->hasEarlyTermination($caseNode->stmts, $switchScope)) {
					$switchScope = $scope;
				}
			}
			return;
		} elseif ($node instanceof ElseIf_) {
			$scope = $this->lookForAssigns($scope, $node->cond);
			$scope = $this->lookForTypeSpecifications($scope, $node->cond);
		} elseif ($node instanceof While_) {
			$scope = $this->lookForAssigns($scope, $node->cond);
			$scope = $this->lookForTypeSpecifications($scope, $node->cond);
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
		} elseif ($node instanceof FuncCall) {
			$scope = $scope->enterFunctionCall($node);
		} elseif ($node instanceof MethodCall) {
			if (
				$scope->getType($node->var)->getClass() === 'Closure'
				&& $node->name === 'call'
				&& isset($node->args[0])
			) {
				$closureCallScope = $scope->enterClosureBind($scope->getType($node->args[0]->value), 'static');
			}
			$scope = $scope->enterFunctionCall($node);
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
		} elseif ($node instanceof BooleanNot) {
			$scope = $scope->enterNegation();
		}

		$originalScope = $scope;
		foreach ($node->getSubNodeNames() as $subNodeName) {
			$scope = $originalScope;
			$subNode = $node->{$subNodeName};

			if (is_array($subNode)) {
				$argClosureBindScope = null;
				if (isset($closureBindScope) && $subNodeName === 'args') {
					$argClosureBindScope = $closureBindScope;
				}
				$this->processNodes($subNode, $scope, $nodeCallback, $argClosureBindScope);
			} elseif ($subNode instanceof \PhpParser\Node) {
				if ($node instanceof Coalesce && $subNodeName === 'left') {
					$scope = $this->assignVariable($scope, $subNode);
				}

				if ($node instanceof Ternary && $subNodeName === 'if') {
					$scope = $this->lookForTypeSpecifications($scope, $node->cond);
				}

				if ($node instanceof BooleanAnd && $subNodeName === 'right') {
					$scope = $this->lookForTypeSpecifications($scope, $node->left);
				}

				if (($node instanceof Assign || $node instanceof AssignRef) && $subNodeName === 'var') {
					$scope = $this->lookForEnterVariableAssign($scope, $node->var);
				}

				$nodeScope = $scope;
				if ($node instanceof MethodCall && $subNodeName === 'var' && isset($closureCallScope)) {
					$nodeScope = $closureCallScope;
				}

				$this->processNode($subNode, $nodeScope, $nodeCallback);
			}
		}
	}

	private function lookForEnterVariableAssign(Scope $scope, Node $node): Scope
	{
		if ($node instanceof Variable && is_string($node->name)) {
			$scope = $scope->enterVariableAssign($node->name);
		} elseif ($node instanceof ArrayDimFetch) {
			while ($node instanceof ArrayDimFetch) {
				$node = $node->var;
			}

			if ($node instanceof Variable && is_string($node->name)) {
				$scope = $scope->enterVariableAssign($node->name);
			}
		} elseif ($node instanceof List_ || $node instanceof Array_) {
			foreach ($node->items as $listItem) {
				if ($listItem === null) {
					continue;
				}
				$scope = $this->lookForEnterVariableAssign($scope, $listItem->value);
			}
		}

		return $scope;
	}

	private function lookForTypeSpecifications(Scope $scope, Node $node): Scope
	{
		$types = $this->typeSpecifier->specifyTypesInCondition(new SpecifiedTypes(), $scope, $node);
		foreach ($types->getSureTypes() as $type) {
			$scope = $scope->specifyExpressionType($type[0], $type[1]);
		}

		return $scope;
	}

	private function lookForTypeSpecificationsInEarlyTermination(Scope $scope, Node $node): Scope
	{
		$types = $this->typeSpecifier->specifyTypesInCondition(new SpecifiedTypes(), $scope, $node);
		foreach ($types->getSureNotTypes() as $type) {
			$scope = $scope->specifyExpressionType($type[0], $type[1]);
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
				new StatementList($scope, $node->finally !== null ? $node->finally->stmts : null),
			];
			foreach ($node->catches as $catch) {
				$statements[] = new StatementList($scope->enterCatch(
					$catch->types,
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

			$parameters = $this->findParametersInFunctionCall($node, $scope);

			if ($parameters !== null) {
				foreach ($parameters as $i => $parameter) {
					if (!isset($node->args[$i]) || !$parameter->isPassedByReference()) {
						continue;
					}

					$arg = $node->args[$i]->value;
					if ($arg instanceof Variable && is_string($arg->name)) {
						$scope = $scope->assignVariable($arg->name, new MixedType(true));
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
			foreach ($node->items as $item) {
				if ($item === null) {
					continue;
				}
				if ($item->value instanceof Variable) {
					$scope = $scope->assignVariable($item->value->name);
				} elseif ($item->value instanceof ArrayDimFetch && $item->value->var instanceof Variable) {
					$scope = $scope->assignVariable($item->value->var->name);
				} else {
					$scope = $this->lookForAssigns($scope, $item->value);
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
		} elseif ($this->polluteScopeWithLoopInitialAssignments) {
			if ($node instanceof For_) {
				foreach ($node->init as $initExpr) {
					$scope = $this->lookForAssigns($scope, $initExpr);
				}

				foreach ($node->cond as $condExpr) {
					$scope = $this->lookForAssigns($scope, $condExpr);
				}
			} elseif ($node instanceof While_) {
				$scope = $this->lookForAssigns($scope, $node->cond);
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
		if (($node instanceof Assign || $node instanceof AssignRef) || $node instanceof Isset_) {
			if ($node instanceof Assign || $node instanceof AssignRef) {
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
					($node instanceof Assign || $node instanceof AssignRef) ? $scope->getType($node->expr) : null
				);
			}

			if ($node instanceof Assign || $node instanceof AssignRef) {
				$scope = $this->lookForAssigns($scope, $node->expr);
				$comment = CommentHelper::getDocComment($node);
				if ($comment !== null && $node->var instanceof Variable && is_string($node->var->name)) {
					$variableName = $node->var->name;
					$processVarAnnotation = function (string $matchedType, string $matchedVariableName) use ($scope, $variableName): Scope {
						$fileTypeMap = $this->fileTypeMapper->getTypeMap($scope->getFile());
						if (isset($fileTypeMap[$matchedType]) && $matchedVariableName === $variableName) {
							return $scope->assignVariable($matchedVariableName, $fileTypeMap[$matchedType]);
						}

						return $scope;
					};

					if (preg_match('#@var\s+' . FileTypeMapper::TYPE_PATTERN . '\s+\$([a-zA-Z0-9_]+)#', $comment, $matches)) {
						$scope = $processVarAnnotation($matches[1], $matches[2]);
					} elseif (preg_match('#@var\s+\$([a-zA-Z0-9_]+)\s+' . FileTypeMapper::TYPE_PATTERN . '#', $comment, $matches)) {
						$scope = $processVarAnnotation($matches[2], $matches[1]);
					}
				}
			}

			if ($node instanceof Isset_) {
				foreach ($vars as $var) {
					if ($var instanceof PropertyFetch) {
						$scope = $scope->specifyFetchedPropertyFromIsset($var);
					}
				}
			}
		}

		return $scope;
	}

	private function assignVariable(Scope $scope, Node $var, Type $subNodeType = null): Scope
	{
		if ($var instanceof Variable) {
			$scope = $scope->assignVariable($var->name, $subNodeType);
		} elseif ($var instanceof ArrayDimFetch) {
			$depth = 0;
			while ($var instanceof ArrayDimFetch) {
				$var = $var->var;
				$depth++;
			}

			if ($var instanceof Variable && is_string($var->name)) {
				$arrayType = ArrayType::createDeepArrayType(
					new NestedArrayItemType($subNodeType !== null ? $subNodeType : new MixedType(true), $depth),
					false
				);
				if ($scope->hasVariableType($var->name)) {
					$arrayType = $scope->getVariableType($var->name)->combineWith($arrayType);
				}

				$scope = $scope->assignVariable($var->name, $arrayType);
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

			$mergeWithPrevious = $isSwitchCase;

			foreach ($statements as $statement) {
				$branchScope = $this->lookForAssigns($branchScope, $statement);
				$hasStatementEarlyTermination = $this->hasStatementEarlyTermination($statement, $branchScope);
				if ($hasStatementEarlyTermination && !$isSwitchCase) {
					continue 2;
				}
			}

			if ($intersectedScope === null) {
				$intersectedScope = $initialScope->addVariables($branchScope);
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
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return bool
	 */
	private function hasEarlyTermination(array $statements, Scope $scope): bool
	{
		foreach ($statements as $statement) {
			if ($this->hasStatementEarlyTermination($statement, $scope)) {
				return true;
			}
		}

		return false;
	}

	private function hasStatementEarlyTermination(Node $statement, Scope $scope): bool
	{
		if (
			$statement instanceof Throw_
			|| $statement instanceof Return_
			|| $statement instanceof Continue_
			|| $statement instanceof Break_
			|| $statement instanceof Exit_
		) {
			return true;
		} elseif ($statement instanceof MethodCall && count($this->earlyTerminatingMethodCalls) > 0) {
			if (!is_string($statement->name)) {
				return false;
			}

			$methodCalledOnType = $scope->getType($statement->var);
			if ($methodCalledOnType->getClass() === null) {
				return false;
			}

			if (!$this->broker->hasClass($methodCalledOnType->getClass())) {
				return false;
			}

			$classReflection = $this->broker->getClass($methodCalledOnType->getClass());
			foreach (array_merge([$methodCalledOnType->getClass()], $classReflection->getParentClassesNames()) as $className) {
				if (!isset($this->earlyTerminatingMethodCalls[$className])) {
					continue;
				}

				return in_array($statement->name, $this->earlyTerminatingMethodCalls[$className], true);
			}

			return false;
		}

		return false;
	}

	/**
	 * @param \PhpParser\Node\Expr $functionCall
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return null|\PHPStan\Reflection\ParameterReflection[]
	 */
	private function findParametersInFunctionCall(Expr $functionCall, Scope $scope)
	{
		if ($functionCall instanceof FuncCall && $functionCall->name instanceof Name) {
			if ($this->broker->hasFunction($functionCall->name, $scope)) {
				return $this->broker->getFunction($functionCall->name, $scope)->getParameters();
			}
		} elseif ($functionCall instanceof MethodCall && is_string($functionCall->name)) {
			$type = $scope->getType($functionCall->var);
			if ($type->getClass() !== null && $this->broker->hasClass($type->getClass())) {
				$classReflection = $this->broker->getClass($type->getClass());
				$methodName = $functionCall->name;
				if ($classReflection->hasMethod((string) $methodName)) {
					return $classReflection->getMethod((string) $methodName)->getParameters();
				}
			}
		}

		return null;
	}

}
