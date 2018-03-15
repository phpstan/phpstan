<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Catch_;
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
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CommentHelper;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

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

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

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

	public function __construct(
		Broker $broker,
		Parser $parser,
		\PhpParser\PrettyPrinter\Standard $printer,
		FileTypeMapper $fileTypeMapper,
		FileHelper $fileHelper,
		TypeSpecifier $typeSpecifier,
		bool $polluteScopeWithLoopInitialAssignments,
		bool $polluteCatchScopeWithTryAssignments,
		array $earlyTerminatingMethodCalls
	)
	{
		$this->broker = $broker;
		$this->parser = $parser;
		$this->printer = $printer;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->fileHelper = $fileHelper;
		$this->typeSpecifier = $typeSpecifier;
		$this->polluteScopeWithLoopInitialAssignments = $polluteScopeWithLoopInitialAssignments;
		$this->polluteCatchScopeWithTryAssignments = $polluteCatchScopeWithTryAssignments;
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
	 * @param \Closure $nodeCallback
	 * @param \PHPStan\Analyser\Scope $closureBindScope
	 */
	public function processNodes(
		array $nodes,
		Scope $scope,
		\Closure $nodeCallback,
		Scope $closureBindScope = null
	): void
	{
		/** @var \PhpParser\Node|string $node */
		foreach ($nodes as $i => $node) {
			if (!($node instanceof \PhpParser\Node)) {
				continue;
			}

			if ($scope->getInFunctionCall() !== null && $node instanceof Arg) {
				$functionCall = $scope->getInFunctionCall();
				$value = $node->value;

				$parametersAcceptor = $this->findParametersAcceptorInFunctionCall($functionCall, $scope);

				if ($parametersAcceptor !== null) {
					$parameters = $parametersAcceptor->getParameters();
					$assignByReference = false;
					if (isset($parameters[$i])) {
						$assignByReference = $parameters[$i]->passedByReference()->createsNewVariable();
					} elseif (count($parameters) > 0 && $parametersAcceptor->isVariadic()) {
						$lastParameter = $parameters[count($parameters) - 1];
						$assignByReference = $lastParameter->passedByReference()->createsNewVariable();
					}
					if ($assignByReference && $value instanceof Variable && is_string($value->name)) {
						$scope = $scope->assignVariable($value->name, new MixedType(), TrinaryLogic::createYes());
					}
				}
			}

			$nodeScope = $scope;
			if ($i === 0 && $closureBindScope !== null) {
				$nodeScope = $closureBindScope;
			}

			$this->processNode($node, $nodeScope, $nodeCallback);
			$scope = $this->lookForAssigns($scope, $node, TrinaryLogic::createYes());

			if ($node instanceof If_) {
				if ($this->findEarlyTermination($node->stmts, $scope) !== null) {
					$scope = $scope->filterByFalseyValue($node->cond);
					$this->processNode($node->cond, $scope, function (Node $node, Scope $inScope) use (&$scope): void {
						$this->specifyFetchedPropertyForInnerScope($node, $inScope, true, $scope);
					});
				}
			} elseif ($node instanceof Node\Stmt\Declare_) {
				foreach ($node->declares as $declare) {
					if (
						$declare->key === 'strict_types'
						&& $declare->value instanceof Node\Scalar\LNumber
						&& $declare->value->value === 1
					) {
						$scope = $scope->enterDeclareStrictTypes();
						break;
					}
				}
			} elseif ($node instanceof FuncCall && $node->name instanceof Name) {
				if ($this->broker->hasFunction($node->name, $scope)) {
					$functionReflection = $this->broker->getFunction($node->name, $scope);
					foreach ($this->typeSpecifier->getFunctionTypeSpecifyingExtensions() as $extension) {
						if (!$extension->isFunctionSupported($functionReflection, $node, TypeSpecifierContext::createNull())) {
							continue;
						}

						$scope = $scope->filterBySpecifiedTypes($extension->specifyTypes($functionReflection, $node, $scope, TypeSpecifierContext::createNull()));
						break;
					}
				}
			} elseif ($node instanceof MethodCall && is_string($node->name)) {
				$methodCalledOnType = $scope->getType($node->var);
				$referencedClasses = $methodCalledOnType->getReferencedClasses();
				if (
					count($referencedClasses) === 1
					&& $this->broker->hasClass($referencedClasses[0])
				) {
					$methodClassReflection = $this->broker->getClass($referencedClasses[0]);
					if ($methodClassReflection->hasMethod($node->name)) {
						$methodReflection = $methodClassReflection->getMethod($node->name, $scope);
						foreach ($this->typeSpecifier->getMethodTypeSpecifyingExtensionsForClass($methodClassReflection->getName()) as $extension) {
							if (!$extension->isMethodSupported($methodReflection, $node, TypeSpecifierContext::createNull())) {
								continue;
							}

							$scope = $scope->filterBySpecifiedTypes($extension->specifyTypes($methodReflection, $node, $scope, TypeSpecifierContext::createNull()));
							break;
						}
					}
				}
			} elseif ($node instanceof StaticCall && is_string($node->name)) {
				if ($node->class instanceof Name) {
					$calleeType = new ObjectType($scope->resolveName($node->class));
				} else {
					$calleeType = $scope->getType($node->class);
				}

				if ($calleeType->hasMethod($node->name)) {
					$staticMethodReflection = $calleeType->getMethod($node->name, $scope);
					$referencedClasses = $calleeType->getReferencedClasses();
					if (
						count($calleeType->getReferencedClasses()) === 1
						&& $this->broker->hasClass($referencedClasses[0])
					) {
						$staticMethodClassReflection = $this->broker->getClass($referencedClasses[0]);
						foreach ($this->typeSpecifier->getStaticMethodTypeSpecifyingExtensionsForClass($staticMethodClassReflection->getName()) as $extension) {
							if (!$extension->isStaticMethodSupported($staticMethodReflection, $node, TypeSpecifierContext::createNull())) {
								continue;
							}

							$scope = $scope->filterBySpecifiedTypes($extension->specifyTypes($staticMethodReflection, $node, $scope, TypeSpecifierContext::createNull()));
							break;
						}
					}
				}
			}
		}
	}

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
		}

		return $scope;
	}

	private function specifyFetchedPropertyForInnerScope(Node $node, Scope $inScope, bool $inEarlyTermination, Scope &$scope): void
	{
		if ($inEarlyTermination === $inScope->isNegated()) {
			if ($node instanceof Isset_) {
				foreach ($node->vars as $var) {
					$scope = $this->specifyProperty($scope, $var);
				}
			} elseif (
				$node instanceof FuncCall
				&& $node->name instanceof Name
				&& $this->broker->resolveFunctionName($node->name, $scope) === 'property_exists'
				&& count($node->args) === 2
			) {
				$secondArgumentType = $scope->getType($node->args[1]->value);

				if ($secondArgumentType instanceof ConstantStringType) {
					$scope = $scope->specifyFetchedPropertyFromIsset(
						new PropertyFetch($node->args[0]->value, $secondArgumentType->getValue())
					);
				}
			}
		} else {
			if ($node instanceof Expr\Empty_) {
				$scope = $this->specifyProperty($scope, $node->expr);
				$scope = $this->assignVariable($scope, $node->expr, TrinaryLogic::createYes());
			}
		}
	}

	private function lookForArrayDestructuringArray(Scope $scope, Node $node): Scope
	{
		if ($node instanceof Array_) {
			foreach ($node->items as $item) {
				if ($item === null) {
					continue;
				}
				$scope = $this->lookForArrayDestructuringArray($scope, $item->value);
			}
		} elseif ($node instanceof Variable && is_string($node->name)) {
			$scope = $scope->assignVariable($node->name, new MixedType(), TrinaryLogic::createYes());
		} elseif ($node instanceof ArrayDimFetch && $node->var instanceof Variable && is_string($node->var->name)) {
			$scope = $scope->assignVariable(
				$node->var->name,
				new MixedType(),
				TrinaryLogic::createYes()
			);
		} elseif ($node instanceof List_) {
			foreach ($node->items as $item) {
				/** @var \PhpParser\Node\Expr\ArrayItem|null $itemValue */
				$itemValue = $item;
				if ($itemValue === null) {
					continue;
				}
				$itemValue = $itemValue->value;
				if ($itemValue instanceof Variable && is_string($itemValue->name)) {
					$scope = $scope->assignVariable($itemValue->name, new MixedType(), TrinaryLogic::createYes());
				} else {
					$scope = $this->lookForArrayDestructuringArray($scope, $itemValue);
				}
			}
		}

		return $scope;
	}

	private function enterForeach(Scope $scope, Foreach_ $node): Scope
	{
		if ($node->keyVar !== null && $node->keyVar instanceof Variable && is_string($node->keyVar->name)) {
			$scope = $scope->assignVariable($node->keyVar->name, new MixedType(), TrinaryLogic::createYes());
		}

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
			$comment = CommentHelper::getDocComment($node);
			if ($comment !== null) {
				$scope = $this->processVarAnnotation($scope, $node->valueVar->name, $comment, true);
			}
		}

		if ($node->valueVar instanceof List_ || $node->valueVar instanceof Array_) {
			$scope = $this->lookForArrayDestructuringArray($scope, $node->valueVar);
		}

		return $this->lookForAssigns($scope, $node->valueVar, TrinaryLogic::createYes());
	}

	private function processNode(\PhpParser\Node $node, Scope $scope, \Closure $nodeCallback, bool $stopImmediately = false): void
	{
		$nodeCallback($node, $scope);
		if ($stopImmediately) {
			return;
		}

		if (
			$node instanceof \PhpParser\Node\Stmt\ClassLike
		) {
			if ($node instanceof Node\Stmt\Trait_) {
				return;
			}
			if (isset($node->namespacedName)) {
				$scope = $scope->enterClass($this->broker->getClass((string) $node->namespacedName));
			} elseif ($this->anonymousClassReflection !== null) {
				$scope = $scope->enterAnonymousClass($this->anonymousClassReflection);
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}
		} elseif ($node instanceof Node\Stmt\TraitUse) {
			$this->processTraitUse($node, $scope, $nodeCallback);
		} elseif ($node instanceof \PhpParser\Node\Stmt\Function_) {
			$scope = $this->enterFunction($scope, $node);
		} elseif ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
			$scope = $this->enterClassMethod($scope, $node);
		} elseif ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
			$scope = $scope->enterNamespace((string) $node->name);
		} elseif (
			$node instanceof \PhpParser\Node\Expr\StaticCall
			&& $node->class instanceof \PhpParser\Node\Name
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
				if (count($argValueType->getReferencedClasses()) === 1) {
					$scopeClass = $argValueType->getReferencedClasses()[0];
				} elseif (
					$argValue instanceof Expr\ClassConstFetch
					&& strtolower($argValue->name) === 'class'
					&& $argValue->class instanceof Name
				) {
					$scopeClass = $scope->resolveName($argValue->class);
				} elseif ($argValueType instanceof ConstantStringType) {
					$scopeClass = $argValueType->getValue();
				}
			}
			$closureBindScope = $scope->enterClosureBind($thisType, $scopeClass);
		} elseif ($node instanceof Foreach_) {
			$scope = $scope->exitFirstLevelStatements();
			$this->processNode($node->expr, $scope, $nodeCallback);
			$scope = $this->lookForAssigns($scope, $node->expr, TrinaryLogic::createYes());
			$scope = $this->enterForeach($scope, $node);
			if ($node->keyVar !== null) {
				$this->processNode($node->keyVar, $scope, $nodeCallback);
			}

			$this->processNode($node->valueVar, $scope, $nodeCallback);

			$scope = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->stmts),
				new StatementList($scope, []),
			], LookForAssignsSettings::insideLoop());
			$scope = $this->enterForeach($scope, $node);

			$this->processNodes($node->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);

			return;
		} elseif ($node instanceof For_) {
			$this->processNodes($node->init, $scope, $nodeCallback);

			foreach ($node->init as $initExpr) {
				$scope = $this->lookForAssigns($scope, $initExpr, TrinaryLogic::createYes(), LookForAssignsSettings::insideLoop());
			}
			$scopeLoopMightHaveRun = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->cond),
				new StatementList($scope, $node->stmts),
				new StatementList($scope, $node->loop),
				new StatementList($scope, []),
			], LookForAssignsSettings::insideLoop());

			$this->processNodes($node->cond, $scopeLoopMightHaveRun, $nodeCallback);

			foreach ($node->cond as $condExpr) {
				$scope = $scope->filterByTruthyValue($condExpr);
			}

			$scopeLoopDefinitelyRan = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->stmts),
			], LookForAssignsSettings::insideLoop());

			$this->processNodes($node->loop, $scopeLoopDefinitelyRan, $nodeCallback);

			foreach ($node->cond as $condExpr) {
				$scopeLoopMightHaveRun = $scopeLoopMightHaveRun->filterByTruthyValue($condExpr);
			}
			$this->processNodes($node->stmts, $scopeLoopMightHaveRun, $nodeCallback);

			return;
		} elseif ($node instanceof While_) {
			$bodyScope = $scope->filterByTruthyValue($node->cond);
			$condScope = $this->lookForAssignsInBranches($scope, [
				new StatementList($bodyScope, $node->stmts),
				new StatementList($scope, []),
			], LookForAssignsSettings::insideLoop());
			$this->processNode($node->cond, $condScope, $nodeCallback);

			$bodyScope = $this->lookForAssignsInBranches($bodyScope, [
				new StatementList($bodyScope, $node->stmts),
				new StatementList($bodyScope, []),
			], LookForAssignsSettings::insideLoop());
			$bodyScope = $this->lookForAssigns($bodyScope, $node->cond, TrinaryLogic::createYes(), LookForAssignsSettings::insideLoop());
			$bodyScope = $bodyScope->filterByTruthyValue($node->cond);
			$this->processNodes($node->stmts, $bodyScope, $nodeCallback);
			return;
		} elseif ($node instanceof Catch_) {
			$scope = $scope->enterCatch(
				$node->types,
				$node->var
			);
		} elseif ($node instanceof Array_) {
			$scope = $scope->exitFirstLevelStatements();
			foreach ($node->items as $item) {
				if ($item === null) {
					continue;
				}
				$this->processNode($item, $scope, $nodeCallback);
				if ($item->key !== null) {
					$scope = $this->lookForAssigns($scope, $item->key, TrinaryLogic::createYes());
				}
				$scope = $this->lookForAssigns($scope, $item->value, TrinaryLogic::createYes());
			}

			return;
		} elseif ($node instanceof Expr\Closure) {
			$this->processNodes($node->uses, $scope, $nodeCallback);
			$closureScope = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->stmts),
				new StatementList($scope, []),
			], LookForAssignsSettings::insideFinally());
			foreach ($node->uses as $closureUse) {
				if (!$closureUse->byRef) {
					continue;
				}

				$variableCertainty = $closureScope->hasVariableType($closureUse->var);
				if ($variableCertainty->no()) {
					continue;
				}
				$scope = $scope->assignVariable(
					$closureUse->var,
					$closureScope->getVariableType($closureUse->var),
					$variableCertainty
				);
			}
			$scope = $scope->enterAnonymousFunction($node->params, $node->uses, $node->returnType);
			$this->processNodes($node->stmts, $scope, $nodeCallback);

			return;
		} elseif ($node instanceof If_) {
			$this->processNode($node->cond, $scope->exitFirstLevelStatements(), $nodeCallback);
			$scope = $this->lookForAssigns(
				$scope,
				$node->cond,
				TrinaryLogic::createYes()
			);
			$ifScope = $scope;
			$scope = $scope->filterByTruthyValue($node->cond);

			$specifyFetchedProperty = function (Node $node, Scope $inScope) use (&$scope): void {
				$this->specifyFetchedPropertyForInnerScope($node, $inScope, false, $scope);
			};
			$this->processNode($node->cond, $scope, $specifyFetchedProperty);
			$this->processNodes($node->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);

			$elseifScope = $ifScope->filterByFalseyValue($node->cond);
			foreach ($node->elseifs as $elseif) {
				$scope = $elseifScope;
				$this->processNode($elseif, $scope, $nodeCallback, true);
				$this->processNode($elseif->cond, $scope->exitFirstLevelStatements(), $nodeCallback);
				$scope = $this->lookForAssigns(
					$scope,
					$elseif->cond,
					TrinaryLogic::createYes()
				);
				$scope = $scope->filterByTruthyValue($elseif->cond);
				$this->processNode($elseif->cond, $scope, $specifyFetchedProperty);
				$this->processNodes($elseif->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);
				$elseifScope = $this->lookForAssigns(
					$elseifScope,
					$elseif->cond,
					TrinaryLogic::createYes()
				)->filterByFalseyValue($elseif->cond);
			}
			if ($node->else !== null) {
				$this->processNode($node->else, $elseifScope, $nodeCallback);
			}

			return;
		} elseif ($node instanceof Switch_) {
			$scope = $scope->exitFirstLevelStatements();
			$this->processNode($node->cond, $scope, $nodeCallback);
			$scope = $this->lookForAssigns(
				$scope,
				$node->cond,
				TrinaryLogic::createYes()
			);
			$switchScope = $scope;
			$switchConditionIsTrue = $node->cond instanceof Expr\ConstFetch && strtolower((string) $node->cond->name) === 'true';
			$switchConditionGetClassExpression = null;
			if (
				$node->cond instanceof FuncCall
				&& $node->cond->name instanceof Name
				&& strtolower((string) $node->cond->name) === 'get_class'
				&& isset($node->cond->args[0])
			) {
				$switchConditionGetClassExpression = $node->cond->args[0]->value;
			}
			foreach ($node->cases as $caseNode) {
				$this->processNode($caseNode, $scope, $nodeCallback, true);
				if ($caseNode->cond !== null) {
					$this->processNode($caseNode->cond, $switchScope, $nodeCallback);
					$switchScope = $this->lookForAssigns(
						$switchScope,
						$caseNode->cond,
						TrinaryLogic::createYes()
					);
					$scope = $this->lookForAssigns(
						$scope,
						$caseNode->cond,
						TrinaryLogic::createYes()
					);

					$caseScope = $switchScope;
					if ($switchConditionIsTrue) {
						$caseScope = $caseScope->filterByTruthyValue($caseNode->cond);
					} elseif (
						$switchConditionGetClassExpression !== null
						&& $caseNode->cond instanceof Expr\ClassConstFetch
						&& $caseNode->cond->class instanceof Name
						&& strtolower($caseNode->cond->name) === 'class'
					) {
						$caseScope = $caseScope->specifyExpressionType(
							$switchConditionGetClassExpression,
							new ObjectType($scope->resolveName($caseNode->cond->class))
						);
					}
				} else {
					$caseScope = $switchScope;
				}
				$this->processNodes(
					$caseNode->stmts,
					$caseScope->enterFirstLevelStatements(),
					$nodeCallback
				);
				if ($this->findEarlyTermination($caseNode->stmts, $switchScope) === null) {
					foreach ($caseNode->stmts as $statement) {
						$switchScope = $this->lookForAssigns($switchScope, $statement, TrinaryLogic::createMaybe());
					}
				} else {
					$switchScope = $scope;
				}
			}
			return;
		} elseif ($node instanceof TryCatch) {
			$statements = [];
			$this->processNodes($node->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);

			$scopeForLookForAssignsInBranches = $scope;
			$tryAssignmentsCertainty = $this->polluteCatchScopeWithTryAssignments ? TrinaryLogic::createYes() : TrinaryLogic::createMaybe();
			foreach ($node->stmts as $statement) {
				$scope = $this->lookForAssigns($scope, $statement, $tryAssignmentsCertainty);
			}

			if ($node->finally !== null) {
				$statements[] = new StatementList($scopeForLookForAssignsInBranches, $node->stmts);
				$statements[] = new StatementList($scopeForLookForAssignsInBranches, []);
			}

			foreach ($node->catches as $catch) {
				$this->processNode($catch, $scope, $nodeCallback);
				if ($node->finally === null) {
					continue;
				}

				$statements[] = new StatementList($scope->enterCatch(
					$catch->types,
					$catch->var
				), $catch->stmts);
			}

			if ($node->finally !== null) {
				$finallyScope = $this->lookForAssignsInBranches($scopeForLookForAssignsInBranches, $statements, LookForAssignsSettings::insideFinally());

				$this->processNode($node->finally, $finallyScope, $nodeCallback);
			}

			return;
		} elseif ($node instanceof FuncCall) {
			$scope = $scope->enterFunctionCall($node);
		} elseif ($node instanceof Expr\StaticCall) {
			$scope = $scope->enterFunctionCall($node);
		} elseif ($node instanceof MethodCall) {
			if (
				$scope->getType($node->var)->describe() === \Closure::class
				&& $node->name === 'call'
				&& isset($node->args[0])
			) {
				$closureCallScope = $scope->enterClosureBind($scope->getType($node->args[0]->value), 'static');
			}
			$scope = $scope->enterFunctionCall($node);
		} elseif ($node instanceof New_ && $node->class instanceof Class_) {
			do {
				$uniqidClass = 'AnonymousClass' . uniqid();
			} while (class_exists('\\' . $uniqidClass));

			$classNode = $node->class;
			$classNode->name = $uniqidClass;
			eval($this->printer->prettyPrint([$classNode]));
			unset($classNode);

			$classReflection = new \ReflectionClass('\\' . $uniqidClass);
			$this->anonymousClassReflection = $this->broker->getClassFromReflection(
				$classReflection,
				sprintf('class@anonymous%s:%s', $scope->getFile(), $node->getLine()),
				true
			);
		} elseif ($node instanceof BooleanNot) {
			$scope = $scope->enterNegation();
		} elseif ($node instanceof Unset_ || $node instanceof Isset_) {
			foreach ($node->vars as $unsetVar) {
				while (
					$unsetVar instanceof ArrayDimFetch
					|| $unsetVar instanceof PropertyFetch
					|| (
						$unsetVar instanceof StaticPropertyFetch
						&& $unsetVar->class instanceof Expr
					)
				) {
					if ($unsetVar instanceof StaticPropertyFetch) {
						$unsetVar = $unsetVar->class;
					} else {
						$unsetVar = $unsetVar->var;
					}
				}

				$scope = $scope->enterExpressionAssign($unsetVar);
			}
		} elseif ($node instanceof Node\Stmt\Global_) {
			foreach ($node->vars as $var) {
				$scope = $scope->enterExpressionAssign($var);
			}
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
				if ($subNodeName === 'stmts') {
					$scope = $scope->enterFirstLevelStatements();
				} else {
					$scope = $scope->exitFirstLevelStatements();
				}

				if ($node instanceof Isset_ && $subNodeName === 'vars') {
					foreach ($node->vars as $issetVar) {
						$scope = $this->specifyProperty($scope, $issetVar);
					}
				}

				if ($node instanceof MethodCall && $subNodeName === 'args') {
					$scope = $this->lookForAssigns($scope, $node->var, TrinaryLogic::createYes());
				}

				if ($node instanceof Do_ && $subNodeName === 'stmts') {
					$scope = $this->lookForAssignsInBranches($scope, [
						new StatementList($scope, $node->stmts),
						new StatementList($scope, [$node->cond], true),
						new StatementList($scope, []),
					], LookForAssignsSettings::insideLoop());
				}

				if ($node instanceof Isset_ && $subNodeName === 'vars') {
					foreach ($subNode as $issetVar) {
						$scope = $this->ensureNonNullability($scope, $issetVar);
					}
				}

				$this->processNodes($subNode, $scope, $nodeCallback, $argClosureBindScope);
			} elseif ($subNode instanceof \PhpParser\Node) {
				if ($node instanceof Coalesce && $subNodeName === 'left') {
					$scope = $this->ensureNonNullability($scope, $subNode);
				}

				if (
					($node instanceof BooleanAnd || $node instanceof BinaryOp\LogicalAnd)
					&& $subNodeName === 'right') {
					$scope = $scope->filterByTruthyValue($node->left);
				}
				if (
					($node instanceof BooleanOr || $node instanceof BinaryOp\LogicalOr)
					&& $subNodeName === 'right') {
					$scope = $scope->filterByFalseyValue($node->left);
				}

				if (($node instanceof Assign || $node instanceof AssignRef) && $subNodeName === 'var') {
					$scope = $this->lookForEnterVariableAssign($scope, $node->var);
				}

				if ($node instanceof BinaryOp && $subNodeName === 'right') {
					$scope = $this->lookForAssigns($scope, $node->left, TrinaryLogic::createYes());
				}

				if ($node instanceof Expr\Empty_ && $subNodeName === 'expr') {
					$scope = $this->specifyProperty($scope, $node->expr);
					$scope = $this->lookForEnterVariableAssign($scope, $node->expr);
				}

				if (
					$node instanceof ArrayItem
					&& $subNodeName === 'value'
					&& $node->key !== null
				) {
					$scope = $this->lookForAssigns($scope, $node->key, TrinaryLogic::createYes());
				}

				if (
					$node instanceof Ternary
					&& $subNodeName !== 'cond'
				) {
					$scope = $this->lookForAssigns($scope, $node->cond, TrinaryLogic::createYes());
					if ($subNodeName === 'if') {
						$scope = $scope->filterByTruthyValue($node->cond);
						$this->processNode($node->cond, $scope, function (Node $node, Scope $inScope) use (&$scope): void {
							$this->specifyFetchedPropertyForInnerScope($node, $inScope, false, $scope);
						});
					} elseif ($subNodeName === 'else') {
						$scope = $scope->filterByFalseyValue($node->cond);
						$this->processNode($node->cond, $scope, function (Node $node, Scope $inScope) use (&$scope): void {
							$this->specifyFetchedPropertyForInnerScope($node, $inScope, true, $scope);
						});
					}
				}

				if ($node instanceof Do_ && $subNodeName === 'cond') {
					foreach ($node->stmts as $statement) {
						$scope = $this->lookForAssigns($scope, $statement, TrinaryLogic::createYes());
					}
				}

				if ($node instanceof Expr\Empty_ && $subNodeName === 'expr') {
					$scope = $this->ensureNonNullability($scope, $subNode);
				}

				$nodeScope = $scope->exitFirstLevelStatements();
				if ($scope->isInFirstLevelStatement()) {
					if ($node instanceof Ternary && $subNodeName !== 'cond') {
						$nodeScope = $scope->enterFirstLevelStatements();
					} elseif (
						($node instanceof BooleanAnd || $node instanceof BinaryOp\BooleanOr)
						&& $subNodeName === 'right'
					) {
						$nodeScope = $scope->enterFirstLevelStatements();
					}
				}

				if ($node instanceof MethodCall && $subNodeName === 'var' && isset($closureCallScope)) {
					$nodeScope = $closureCallScope->exitFirstLevelStatements();
				}

				$this->processNode($subNode, $nodeScope, $nodeCallback);
			}
		}
	}

	private function ensureNonNullability(Scope $scope, Node $node): Scope
	{
		$scope = $this->assignVariable($scope, $node, TrinaryLogic::createYes());
		$nodeToSpecify = $node;
		while (
			$nodeToSpecify instanceof PropertyFetch
			|| $nodeToSpecify instanceof MethodCall
		) {
			$nodeToSpecify = $nodeToSpecify->var;
			$scope = $scope->specifyExpressionType(
				$nodeToSpecify,
				TypeCombinator::removeNull($scope->getType($nodeToSpecify))
			);
		}

		return $scope;
	}

	private function lookForEnterVariableAssign(Scope $scope, Expr $node): Scope
	{
		if ($node instanceof Variable) {
			$scope = $scope->enterExpressionAssign($node);
		} elseif ($node instanceof ArrayDimFetch) {
			while ($node instanceof ArrayDimFetch) {
				$scope = $scope->enterExpressionAssign($node);
				$node = $node->var;
			}

			if ($node instanceof Variable) {
				$scope = $scope->enterExpressionAssign($node);
			}
		} elseif ($node instanceof List_ || $node instanceof Array_) {
			foreach ($node->items as $listItem) {
				if ($listItem === null) {
					continue;
				}

				$scope = $this->lookForEnterVariableAssign($scope, $listItem->value);
			}
		} else {
			$scope = $scope->enterExpressionAssign($node);
		}

		return $scope;
	}

	private function lookForAssigns(
		Scope $scope,
		\PhpParser\Node $node,
		TrinaryLogic $certainty,
		LookForAssignsSettings $lookForAssignsSettings = null
	): Scope
	{
		if ($lookForAssignsSettings === null) {
			$lookForAssignsSettings = LookForAssignsSettings::default();
		}
		if ($node instanceof StaticVar) {
			$scope = $scope->assignVariable(
				$node->name,
				$node->default !== null ? $scope->getType($node->default) : new MixedType(),
				$certainty
			);
		} elseif ($node instanceof Static_) {
			foreach ($node->vars as $var) {
				$scope = $this->lookForAssigns($scope, $var, $certainty);
			}
		} elseif ($node instanceof If_) {
			$scope = $this->lookForAssigns($scope, $node->cond, $certainty);
			$ifStatement = new StatementList(
				$scope->filterByTruthyValue($node->cond),
				array_merge([$node->cond], $node->stmts)
			);

			$elseIfScope = $scope->filterByFalseyValue($node->cond);
			$elseIfStatements = [];
			foreach ($node->elseifs as $elseIf) {
				$elseIfStatements[] = new StatementList(
					$elseIfScope->filterByTruthyValue($elseIf->cond),
					array_merge([$elseIf->cond], $elseIf->stmts)
				);
				$elseIfScope = $elseIfScope->filterByFalseyValue($elseIf->cond);
			}

			$statements = array_merge(
				[$ifStatement],
				$elseIfStatements,
				[new StatementList($elseIfScope, $node->else !== null ? $node->else->stmts : [])]
			);

			$scope = $this->lookForAssignsInBranches($scope, $statements, $lookForAssignsSettings);
		} elseif ($node instanceof TryCatch) {
			$statements = [
				new StatementList($scope, $node->stmts),
			];
			foreach ($node->catches as $catch) {
				$statements[] = new StatementList($scope->enterCatch(
					$catch->types,
					$catch->var
				), array_merge([new Node\Stmt\Nop()], $catch->stmts));
			}

			$scope = $this->lookForAssignsInBranches($scope, $statements, $lookForAssignsSettings);
			if ($node->finally !== null) {
				foreach ($node->finally->stmts as $statement) {
					$scope = $this->lookForAssigns($scope, $statement, $certainty, $lookForAssignsSettings);
				}
			}
		} elseif ($node instanceof MethodCall || $node instanceof FuncCall || $node instanceof Expr\StaticCall) {
			if ($node instanceof MethodCall) {
				$scope = $this->lookForAssigns($scope, $node->var, $certainty);
			}
			foreach ($node->args as $argument) {
				$scope = $this->lookForAssigns($scope, $argument, $certainty);
			}

			$parametersAcceptor = $this->findParametersAcceptorInFunctionCall($node, $scope);

			if ($parametersAcceptor !== null) {
				$parameters = $parametersAcceptor->getParameters();
				foreach ($node->args as $i => $arg) {
					$assignByReference = false;
					if (isset($parameters[$i])) {
						$assignByReference = $parameters[$i]->passedByReference()->createsNewVariable();
						$parameterType = $parameters[$i]->getType();
					} elseif (count($parameters) > 0 && $parametersAcceptor->isVariadic()) {
						$lastParameter = $parameters[count($parameters) - 1];
						$assignByReference = $lastParameter->passedByReference()->createsNewVariable();
					}

					if (!$assignByReference) {
						continue;
					}

					$arg = $node->args[$i]->value;
					if (!($arg instanceof Variable) || !is_string($arg->name)) {
						continue;
					}

					$scope = $scope->assignVariable($arg->name, $parameterType ?? new MixedType(), $certainty);
				}
			}
			if (
				$node instanceof FuncCall
				&& $node->name instanceof Name
				&& in_array((string) $node->name, [
					'fopen',
					'file_get_contents',
				], true)
			) {
				$scope = $scope->assignVariable('http_response_header', new ArrayType(new IntegerType(), new StringType(), false), $certainty);
			}
		} elseif ($node instanceof BinaryOp) {
			$scope = $this->lookForAssigns($scope, $node->left, $certainty);
			$scope = $this->lookForAssigns($scope, $node->right, $certainty);
		} elseif ($node instanceof Arg) {
			$scope = $this->lookForAssigns($scope, $node->value, $certainty);
		} elseif ($node instanceof BooleanNot) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
		} elseif ($node instanceof Ternary) {
			$scope = $this->lookForAssigns($scope, $node->cond, $certainty);
			$statements = [];
			if ($node->if !== null) {
				$statements[] = new StatementList(
					$scope->filterByTruthyValue($node->cond),
					[$node->if]
				);
			} else {
				$statements[] = new StatementList(
					$scope->filterByTruthyValue($node->cond),
					[$node->cond]
				);
			}

			$statements[] = new StatementList(
				$scope->filterByFalseyValue($node->cond),
				[$node->else]
			);
			$scope = $this->lookForAssignsInBranches($scope, $statements, $lookForAssignsSettings);
		} elseif ($node instanceof Array_) {
			foreach ($node->items as $item) {
				if ($item === null) {
					continue;
				}
				if ($item->key !== null) {
					$scope = $this->lookForAssigns($scope, $item->key, $certainty);
				}
				$scope = $this->lookForAssigns($scope, $item->value, $certainty);
			}
		} elseif ($node instanceof New_) {
			foreach ($node->args as $arg) {
				$scope = $this->lookForAssigns($scope, $arg, $certainty);
			}
		} elseif ($node instanceof Do_) {
			$scope = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->stmts),
			], LookForAssignsSettings::afterLoop());
			$scope = $this->lookForAssigns($scope, $node->cond, TrinaryLogic::createYes(), LookForAssignsSettings::afterLoop());
		} elseif ($node instanceof Switch_) {
			$statementLists = [];
			$tmpStatements = [];
			$hasDefault = false;
			foreach ($node->cases as $case) {
				if ($case->cond === null) {
					$hasDefault = true;
				}

				foreach ($case->stmts as $statement) {
					$tmpStatements[] = $statement;
					if ($this->findStatementEarlyTermination($statement, $scope) !== null) {
						$statementLists[] = new StatementList($scope, $tmpStatements);
						$tmpStatements = [];
						break;
					}
				}
			}

			if (count($tmpStatements) > 0) {
				$statementLists[] = new StatementList($scope, $tmpStatements);
			}

			if (!$hasDefault) {
				$statementLists[] = new StatementList($scope, []);
			}

			$scope = $this->lookForAssignsInBranches($scope, $statementLists, LookForAssignsSettings::afterLoop());
		} elseif ($node instanceof Cast) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
		} elseif ($node instanceof For_) {
			$forAssignmentsCertainty = $this->polluteScopeWithLoopInitialAssignments ? TrinaryLogic::createYes() : TrinaryLogic::createMaybe();
			foreach ($node->init as $initExpr) {
				$scope = $this->lookForAssigns($scope, $initExpr, $forAssignmentsCertainty, LookForAssignsSettings::afterLoop());
			}

			foreach ($node->cond as $condExpr) {
				$scope = $this->lookForAssigns($scope, $condExpr, $forAssignmentsCertainty, LookForAssignsSettings::afterLoop());
			}

			$statements = [
				new StatementList($scope, $node->stmts),
				new StatementList($scope, []), // in order not to add variables existing only inside the for loop
			];
			$scope = $this->lookForAssignsInBranches($scope, $statements, LookForAssignsSettings::afterLoop());
			foreach ($node->loop as $loopExpr) {
				$scope = $this->lookForAssigns($scope, $loopExpr, TrinaryLogic::createMaybe(), LookForAssignsSettings::afterLoop());
			}
		} elseif ($node instanceof While_) {
			$whileAssignmentsCertainty = $this->polluteScopeWithLoopInitialAssignments ? TrinaryLogic::createYes() : TrinaryLogic::createMaybe();
			$scope = $this->lookForAssigns($scope, $node->cond, $whileAssignmentsCertainty, LookForAssignsSettings::afterLoop());

			$statements = [
				new StatementList($scope, $node->stmts),
				new StatementList($scope, []), // in order not to add variables existing only inside the for loop
			];
			$scope = $this->lookForAssignsInBranches($scope, $statements, LookForAssignsSettings::afterLoop());
		} elseif ($node instanceof ErrorSuppress) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
		} elseif ($node instanceof \PhpParser\Node\Stmt\Unset_) {
			foreach ($node->vars as $var) {
				$scope = $scope->unsetExpression($var);
			}
		} elseif ($node instanceof Echo_) {
			foreach ($node->exprs as $echoedExpr) {
				$scope = $this->lookForAssigns($scope, $echoedExpr, $certainty);
			}
		} elseif ($node instanceof Print_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
		} elseif ($node instanceof Foreach_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
			$initialScope = $scope;
			$scope = $this->enterForeach($scope, $node);
			$statements = [
				new StatementList($scope, array_merge(
					[new Node\Stmt\Nop()],
					$node->stmts
				)),
				new StatementList($initialScope, []), // in order not to add variables existing only inside the for loop
			];
			$scope = $this->lookForAssignsInBranches($initialScope, $statements, LookForAssignsSettings::afterLoop());
		} elseif ($node instanceof Isset_) {
			foreach ($node->vars as $var) {
				$scope = $this->lookForAssigns($scope, $var, $certainty);
			}
		} elseif ($node instanceof Expr\Empty_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
		} elseif ($node instanceof ArrayDimFetch && $node->dim !== null) {
			$scope = $this->lookForAssigns($scope, $node->dim, $certainty);
		} elseif ($node instanceof Expr\Closure) {
			$closureScope = $scope->enterAnonymousFunction($node->params, $node->uses, $node->returnType);
			$statements = [
				new StatementList($closureScope, array_merge(
					[new Node\Stmt\Nop()],
					$node->stmts
				)),
				new StatementList($closureScope, []),
			];
			$closureScope = $this->lookForAssignsInBranches($scope, $statements, LookForAssignsSettings::insideFinally());
			foreach ($node->uses as $closureUse) {
				if (!$closureUse->byRef) {
					continue;
				}

				$variableCertainty = $closureScope->hasVariableType($closureUse->var);
				if ($variableCertainty->no()) {
					continue;
				}

				$scope = $scope->assignVariable(
					$closureUse->var,
					$closureScope->getVariableType($closureUse->var),
					$variableCertainty
				);
			}
		} elseif ($node instanceof Instanceof_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
		} elseif ($node instanceof Expr\Include_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty);
		} elseif (
			$node instanceof Expr\PostInc
			|| $node instanceof Expr\PostDec
			|| $node instanceof Expr\PreInc
			|| $node instanceof Expr\PreDec
		) {
			if (
				$node->var instanceof Variable
				&& is_string($node->var->name)
			) {
				$variableCertainty = $scope->hasVariableType($node->var->name);
				if (!$variableCertainty->no()) {
					$variableType = $scope->getVariableType($node->var->name);
					if ($variableType instanceof ConstantScalarType) {
						$variableValue = $variableType->getValue();
						if (
							$node instanceof Expr\PostInc
							|| $node instanceof Expr\PreInc
						) {
							$variableValue++;
						} else {
							$variableValue--;
						}

						$newType = $scope->getTypeFromValue($variableValue);
						if (
							$lookForAssignsSettings->shouldGeneralizeConstantTypesOfNonIdempotentOperations()
							&& $newType instanceof ConstantType
						) {
							$newType = $newType->generalize();
						}
						$scope = $this->assignVariable(
							$scope,
							$node->var,
							$variableCertainty,
							$newType
						);
					}
				}
			} elseif (
				$node->var instanceof ArrayDimFetch
				&& $node->var->dim !== null
			) {
				$arrayType = $scope->getType($node->var->var);
				if ($arrayType instanceof ConstantArrayType) {
					$dimType = $scope->getType($node->var->dim);
					$valueType = $arrayType->getOffsetValueType($dimType);
					if ($valueType instanceof ConstantScalarType) {
						$offsetValue = $valueType->getValue();
						if (
							$node instanceof Expr\PreInc
							|| $node instanceof Expr\PostInc
						) {
							$offsetValue++;
						} else {
							$offsetValue--;
						}

						$newType = $scope->getTypeFromValue($offsetValue);
						if (
							$lookForAssignsSettings->shouldGeneralizeConstantTypesOfNonIdempotentOperations()
							&& $newType instanceof ConstantType
						) {
							$newType = $newType->generalize();
						}

						$scope = $scope->specifyExpressionType(
							$node->var->var,
							$arrayType->setOffsetValueType($dimType, $newType)
						);
					}
				}
			}
		}

		$scope = $this->updateScopeForVariableAssign($scope, $node, $certainty, $lookForAssignsSettings);

		return $scope;
	}

	private function updateScopeForVariableAssign(
		Scope $scope,
		\PhpParser\Node $node,
		TrinaryLogic $certainty,
		LookForAssignsSettings $lookForAssignsSettings
	): Scope
	{
		if ($node instanceof Assign || $node instanceof AssignRef || $node instanceof Expr\AssignOp || $node instanceof Node\Stmt\Global_) {
			if ($node instanceof Assign || $node instanceof AssignRef || $node instanceof Expr\AssignOp) {
				$vars = [$node->var];
			} else {
				$vars = $node->vars;
			}

			foreach ($vars as $var) {
				$type = null;
				if ($node instanceof Assign || $node instanceof AssignRef) {
					$type = $scope->getType($node->expr);
				} elseif ($node instanceof Expr\AssignOp) {
					if (
						$node->var instanceof Variable
						&& is_string($node->var->name)
						&& !$scope->hasVariableType($node->var->name)->yes()
					) {
						continue;
					}
					$type = $scope->getType($node);
					if (
						$lookForAssignsSettings->shouldGeneralizeConstantTypesOfNonIdempotentOperations()
						&& $type instanceof ConstantType
					) {
						$type = $type->generalize();
					}
				}

				$scope = $this->assignVariable($scope, $var, $certainty, $type);
			}

			if ($node instanceof Assign || $node instanceof AssignRef) {
				if ($node->var instanceof Array_ || $node->var instanceof List_) {
					$scope = $this->lookForArrayDestructuringArray($scope, $node->var);
				}
			}

			if (!$node instanceof Node\Stmt\Global_) {
				$scope = $this->lookForAssigns($scope, $node->expr, TrinaryLogic::createYes());
			}

			if ($node instanceof Assign || $node instanceof AssignRef) {
				if ($node->var instanceof Variable && is_string($node->var->name)) {
					$comment = CommentHelper::getDocComment($node);
					if ($comment !== null) {
						$scope = $this->processVarAnnotation($scope, $node->var->name, $comment, false);
					}
				}
			}
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
			return $scope->assignVariable($variableName, $variableType, TrinaryLogic::createYes());

		}

		if (!$strict && count($varTags) === 1 && isset($varTags[0])) {
			$variableType = $varTags[0]->getType();
			return $scope->assignVariable($variableName, $variableType, TrinaryLogic::createYes());

		}

		return $scope;
	}

	private function assignVariable(
		Scope $scope,
		Node $var,
		TrinaryLogic $certainty,
		Type $subNodeType = null
	): Scope
	{
		if ($var instanceof Variable && is_string($var->name)) {
			$scope = $scope->assignVariable($var->name, $subNodeType !== null ? $subNodeType : new MixedType(), $certainty);
		} elseif ($var instanceof ArrayDimFetch) {
			$subNodeType = $subNodeType ?? new MixedType();

			$dimExprStack = [];
			while ($var instanceof ArrayDimFetch) {
				$dimExprStack[] = $var->dim;
				$var = $var->var;
			}

			// 1. eval root expr
			$scope = $this->lookForAssigns($scope, $var, TrinaryLogic::createYes());

			// 2. eval dimensions
			$offsetTypes = [];
			foreach (array_reverse($dimExprStack) as $dimExpr) {
				if ($dimExpr === null) {
					$offsetTypes[] = null;

				} else {
					$scope = $this->lookForAssigns($scope, $dimExpr, TrinaryLogic::createYes());
					$offsetTypes[] = $scope->getType($dimExpr);
				}
			}

			// 3. eval assigned expr, unfortunately this was already done

			// 4. compose types
			if ($var instanceof Variable && is_string($var->name)) {
				if (!$scope->hasVariableType($var->name)->no()) {
					$varType = $scope->getVariableType($var->name);

				} else {
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

				$valueToWrite = $subNodeType;
				foreach (array_reverse($offsetTypes) as $offsetType) {
					$offsetValueType = array_pop($offsetValueTypeStack);
					$valueToWrite = $offsetValueType->setOffsetValueType($offsetType, $valueToWrite);
				}

				if ($valueToWrite instanceof ErrorType) {
					$valueToWrite = new ArrayType(new MixedType(), new MixedType(), true);
				}

				$scope = $scope->assignVariable(
					$var->name,
					$valueToWrite,
					$certainty
				);
			}
		} elseif ($var instanceof PropertyFetch && $subNodeType !== null) {
			$scope = $scope->specifyExpressionType($var, $subNodeType);
		} elseif ($var instanceof Expr\StaticPropertyFetch && $subNodeType !== null) {
			$scope = $scope->specifyExpressionType($var, $subNodeType);
		} else {
			$scope = $this->lookForAssigns($scope, $var, TrinaryLogic::createYes());
		}

		return $scope;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $initialScope
	 * @param \PHPStan\Analyser\StatementList[] $statementsLists
	 * @param \PHPStan\Analyser\LookForAssignsSettings $lookForAssignsSettings
	 * @return Scope
	 */
	private function lookForAssignsInBranches(
		Scope $initialScope,
		array $statementsLists,
		LookForAssignsSettings $lookForAssignsSettings
	): Scope
	{
		/** @var \PHPStan\Analyser\Scope|null $intersectedScope */
		$intersectedScope = null;
		foreach ($statementsLists as $i => $statementList) {
			$statements = $statementList->getStatements();
			$branchScope = $statementList->getScope();
			$branchScopeWithInitialScopeRemoved = $branchScope->removeVariables($initialScope, true);

			$earlyTerminationStatement = null;
			foreach ($statements as $statement) {
				$branchScope = $this->lookForAssigns($branchScope, $statement, TrinaryLogic::createYes(), $lookForAssignsSettings);
				$branchScopeWithInitialScopeRemoved = $branchScope->removeVariables($initialScope, false);
				$earlyTerminationStatement = $this->findStatementEarlyTermination($statement, $branchScope);
				if ($earlyTerminationStatement !== null) {
					if ($lookForAssignsSettings->shouldSkipBranch($earlyTerminationStatement)) {
						continue 2;
					}
					break;
				}
			}

			if (!$lookForAssignsSettings->shouldIntersectVariables($earlyTerminationStatement)) {
				continue;
			}

			if ($intersectedScope === null) {
				$intersectedScope = $initialScope->createIntersectedScope($branchScopeWithInitialScopeRemoved);
			} else {
				$intersectedScope = $intersectedScope->intersectVariables($branchScopeWithInitialScopeRemoved);
			}

			if (!$statementList->shouldFilterByTruthyValue()) {
				continue;
			}

			/** @var \PhpParser\Node\Expr $statement */
			foreach ($statements as $statement) {
				$intersectedScope = $intersectedScope->filterByTruthyValue($statement);
			}
		}

		if ($intersectedScope !== null) {
			$scope = $initialScope->mergeWithIntersectedScope($intersectedScope);

			return $scope;
		}

		return $initialScope;
	}

	/**
	 * @param \PhpParser\Node[] $statements
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PhpParser\Node|null
	 */
	private function findEarlyTermination(array $statements, Scope $scope): ?\PhpParser\Node
	{
		foreach ($statements as $statement) {
			$statement = $this->findStatementEarlyTermination($statement, $scope);
			if ($statement !== null) {
				return $statement;
			}
		}

		return null;
	}

	private function findStatementEarlyTermination(Node $statement, Scope $scope): ?\PhpParser\Node
	{
		if (
			$statement instanceof Throw_
			|| $statement instanceof Return_
			|| $statement instanceof Continue_
			|| $statement instanceof Break_
			|| $statement instanceof Exit_
		) {
			return $statement;
		} elseif (($statement instanceof MethodCall || $statement instanceof Expr\StaticCall) && count($this->earlyTerminatingMethodCalls) > 0) {
			if (!is_string($statement->name)) {
				return null;
			}

			if ($statement instanceof MethodCall) {
				$methodCalledOnType = $scope->getType($statement->var);
			} else {
				if ($statement->class instanceof Name) {
					$methodCalledOnType = $scope->getFunctionType($statement->class, false, false);
				} else {
					$methodCalledOnType = $scope->getType($statement->class);
				}
			}

			foreach ($methodCalledOnType->getReferencedClasses() as $referencedClass) {
				if (!$this->broker->hasClass($referencedClass)) {
					continue;
				}

				$classReflection = $this->broker->getClass($referencedClass);
				foreach (array_merge([$referencedClass], $classReflection->getParentClassesNames()) as $className) {
					if (!isset($this->earlyTerminatingMethodCalls[$className])) {
						continue;
					}

					if (in_array($statement->name, $this->earlyTerminatingMethodCalls[$className], true)) {
						return $statement;
					}
				}
			}

			return null;
		} elseif ($statement instanceof If_) {
			if ($statement->else === null) {
				return null;
			}

			if ($this->findEarlyTermination($statement->stmts, $scope) === null) {
				return null;
			}

			foreach ($statement->elseifs as $elseIfStatement) {
				if ($this->findEarlyTermination($elseIfStatement->stmts, $scope) === null) {
					return null;
				}
			}

			if ($this->findEarlyTermination($statement->else->stmts, $scope) === null) {
				return null;
			}

			return $statement;
		}

		return null;
	}

	private function findParametersAcceptorInFunctionCall(Expr $functionCall, Scope $scope): ?\PHPStan\Reflection\ParametersAcceptor
	{
		if ($functionCall instanceof FuncCall && $functionCall->name instanceof Name) {
			if ($this->broker->hasFunction($functionCall->name, $scope)) {
				return $this->broker->getFunction($functionCall->name, $scope);
			}
		} elseif ($functionCall instanceof MethodCall && is_string($functionCall->name)) {
			$type = $scope->getType($functionCall->var);
			$methodName = $functionCall->name;
			if ($type->hasMethod($methodName)) {
				return $type->getMethod($methodName, $scope);
			}
		} elseif (
			$functionCall instanceof Expr\StaticCall
			&& $functionCall->class instanceof Name
			&& is_string($functionCall->name)) {
			$className = $scope->resolveName($functionCall->class);
			if ($this->broker->hasClass($className)) {
				$classReflection = $this->broker->getClass($className);
				if ($classReflection->hasMethod($functionCall->name)) {
					return $classReflection->getMethod($functionCall->name, $scope);
				}
			}
		}

		return null;
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
				throw new \PHPStan\ShouldNotHappenException();
			}
			$fileName = $this->fileHelper->normalizePath($traitFileName);
			if (!isset($this->analysedFiles[$fileName])) {
				return;
			}
			$parserNodes = $this->parser->parseFile($fileName);
			$classScope = $classScope->enterTrait($traitReflection);

			$this->processNodesForTraitUse($parserNodes, $traitName, $classScope, $nodeCallback);
		}
	}

	/**
	 * @param \PhpParser\Node[]|\PhpParser\Node|scalar $node
	 * @param string $traitName
	 * @param \PHPStan\Analyser\Scope $classScope
	 * @param \Closure $nodeCallback
	 */
	private function processNodesForTraitUse($node, string $traitName, Scope $classScope, \Closure $nodeCallback): void
	{
		if ($node instanceof Node) {
			if ($node instanceof Node\Stmt\Trait_ && $traitName === (string) $node->namespacedName) {
				$this->processNodes($node->stmts, $classScope->enterFirstLevelStatements(), $nodeCallback);
				return;
			}
			if ($node instanceof Node\Stmt\ClassLike) {
				return;
			}
			foreach ($node->getSubNodeNames() as $subNodeName) {
				$subNode = $node->{$subNodeName};
				$this->processNodesForTraitUse($subNode, $traitName, $classScope, $nodeCallback);
			}
		} elseif (is_array($node)) {
			foreach ($node as $subNode) {
				$this->processNodesForTraitUse($subNode, $traitName, $classScope, $nodeCallback);
			}
		}
	}

	private function enterClassMethod(Scope $scope, Node\Stmt\ClassMethod $classMethod): Scope
	{
		list($phpDocParameterTypes, $phpDocReturnType) = $this->getPhpDocs($scope, $classMethod);

		return $scope->enterClassMethod(
			$classMethod,
			$phpDocParameterTypes,
			$phpDocReturnType
		);
	}

	private function getPhpDocs(Scope $scope, Node\FunctionLike $functionLike): array
	{
		$phpDocParameterTypes = [];
		$phpDocReturnType = null;
		if ($functionLike->getDocComment() !== null) {
			$docComment = $functionLike->getDocComment()->getText();
			$file = $scope->getFile();
			$class = $scope->isInClass() ? $scope->getClassReflection()->getName() : null;
			$trait = $scope->isInTrait() ? $scope->getTraitReflection()->getName() : null;
			if ($functionLike instanceof Node\Stmt\ClassMethod) {
				if (!$scope->isInClass()) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
					$this->broker,
					$docComment,
					$scope->getClassReflection()->getName(),
					$functionLike->name,
					$file
				);
				$docComment = $phpDocBlock->getDocComment();
				$file = $phpDocBlock->getFile();
				$class = $phpDocBlock->getClass();
			}

			$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
				$file,
				$class,
				$trait,
				$docComment
			);
			$phpDocParameterTypes = array_map(function (ParamTag $tag): Type {
				return $tag->getType();
			}, $resolvedPhpDoc->getParamTags());
			$phpDocReturnType = $resolvedPhpDoc->getReturnTag() !== null ? $resolvedPhpDoc->getReturnTag()->getType() : null;
		}

		return [$phpDocParameterTypes, $phpDocReturnType];
	}

	private function enterFunction(Scope $scope, Node\Stmt\Function_ $function): Scope
	{
		list($phpDocParameterTypes, $phpDocReturnType) = $this->getPhpDocs($scope, $function);

		return $scope->enterFunction(
			$function,
			$phpDocParameterTypes,
			$phpDocReturnType
		);
	}

}
