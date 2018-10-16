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
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CommentHelper;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class NodeScopeResolver
{

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

	/** @var string[][] className(string) => methods(string[]) */
	private $earlyTerminatingMethodCalls;

	/** @var \PHPStan\Reflection\ClassReflection|null */
	private $anonymousClassReflection;

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
	 * @param \PHPStan\Analyser\Scope $closureBindScope
	 */
	public function processNodes(
		array $nodes,
		Scope $scope,
		\Closure $nodeCallback,
		?Scope $closureBindScope = null
	): void
	{
		$nodesCount = count($nodes);
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

			if ($i === $nodesCount - 1) {
				break;
			}
			$scope = $this->lookForAssigns(
				$scope,
				$node,
				TrinaryLogic::createYes(),
				LookForAssignsSettings::default()
			);

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
						$declare->key->name === 'strict_types'
						&& $declare->value instanceof Node\Scalar\LNumber
						&& $declare->value->value === 1
					) {
						$scope = $scope->enterDeclareStrictTypes();
						break;
					}
				}
			} elseif ($node instanceof Node\Stmt\Expression) {
				$scope = $scope->filterBySpecifiedTypes($this->typeSpecifier->specifyTypesInCondition(
					$scope,
					$node->expr,
					TypeSpecifierContext::createNull()
				));
			}
		}
	}

	private function specifyProperty(Scope $scope, Expr $expr): Scope
	{
		if (
			$expr instanceof PropertyFetch
			&& $expr->name instanceof Node\Identifier
		) {
			return $scope->filterByTruthyValue(
				new FuncCall(
					new Node\Name('property_exists'),
					[
						new Arg($expr->var),
						new Arg(new Node\Scalar\String_($expr->name->name)),
					]
				)
			);
		} elseif (
			$expr instanceof Expr\StaticPropertyFetch
		) {
			if (
				$expr->class instanceof Name
			) {
				if ((string) $expr->class === 'static') {
					return $scope->specifyFetchedStaticPropertyFromIsset($expr);
				}
			} elseif ($expr->name instanceof Node\VarLikeIdentifier) {
				return $scope->filterByTruthyValue(
					new FuncCall(
						new Node\Name('property_exists'),
						[
							new Arg($expr->class),
							new Arg(new Node\Scalar\String_($expr->name->name)),
						]
					)
				);
			}
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
			}
		} else {
			if ($node instanceof Expr\Empty_) {
				$scope = $this->specifyProperty($scope, $node->expr);
			}
		}
	}

	private function lookForArrayDestructuringArray(Scope $scope, Node $node, Type $valueType): Scope
	{
		if ($node instanceof Array_ || $node instanceof List_) {
			foreach ($node->items as $key => $item) {
				/** @var \PhpParser\Node\Expr\ArrayItem|null $itemValue */
				$itemValue = $item;
				if ($itemValue === null) {
					continue;
				}

				$keyType = $itemValue->key === null ? new ConstantIntegerType($key) : $scope->getType($itemValue->key);
				$scope = $this->specifyItemFromArrayDestructuring($scope, $itemValue, $valueType, $keyType);
			}
		} elseif ($node instanceof Variable && is_string($node->name)) {
			$scope = $scope->assignVariable($node->name, new MixedType(), TrinaryLogic::createYes());
		} elseif ($node instanceof ArrayDimFetch && $node->var instanceof Variable && is_string($node->var->name)) {
			$scope = $scope->assignVariable(
				$node->var->name,
				new MixedType(),
				TrinaryLogic::createYes()
			);
		}

		return $scope;
	}

	private function specifyItemFromArrayDestructuring(Scope $scope, ArrayItem $arrayItem, Type $valueType, Type $keyType): Scope
	{
		$type = $valueType->getOffsetValueType($keyType);

		$itemNode = $arrayItem->value;
		if ($itemNode instanceof Variable && is_string($itemNode->name)) {
			$scope = $scope->assignVariable($itemNode->name, $type, TrinaryLogic::createYes());
		} elseif ($itemNode instanceof ArrayDimFetch && $itemNode->var instanceof Variable && is_string($itemNode->var->name)) {
			$scope = $scope->assignVariable(
				$itemNode->var->name,
				$type,
				TrinaryLogic::createYes()
			);
		} else {
			$scope = $this->lookForArrayDestructuringArray($scope, $itemNode, $type);
		}

		return $scope;
	}

	private function enterForeach(Scope $scope, Foreach_ $node): Scope
	{
		if ($node->keyVar !== null && $node->keyVar instanceof Variable && is_string($node->keyVar->name)) {
			$scope = $scope->assignVariable($node->keyVar->name, new MixedType(), TrinaryLogic::createYes());
		}

		$comment = CommentHelper::getDocComment($node);
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
			if ($comment !== null) {
				$scope = $this->processVarAnnotation($scope, $node->valueVar->name, $comment, true);
			}
		}

		if (
			$node->keyVar instanceof Variable && is_string($node->keyVar->name)
			&& $comment !== null
		) {
			$scope = $this->processVarAnnotation($scope, $node->keyVar->name, $comment, true);
		}

		if ($node->valueVar instanceof List_ || $node->valueVar instanceof Array_) {
			$itemTypes = [];
			$exprType = $scope->getType($node->expr);
			$arrayTypes = TypeUtils::getArrays($exprType);
			foreach ($arrayTypes as $arrayType) {
				$itemTypes[] = $arrayType->getItemType();
			}

			$itemType = count($itemTypes) > 0 ? TypeCombinator::union(...$itemTypes) : new MixedType();
			$scope = $this->lookForArrayDestructuringArray($scope, $node->valueVar, $itemType);
		}

		return $this->lookForAssigns($scope, $node->valueVar, TrinaryLogic::createYes(), LookForAssignsSettings::default());
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param Scope $scope
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void $nodeCallback
	 * @param bool $stopImmediately
	 */
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
			&& $node->name instanceof Node\Identifier
			&& (string) $node->class === 'Closure'
			&& $node->name->name === 'bind'
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
		} elseif ($node instanceof Foreach_) {
			$scope = $scope->exitFirstLevelStatements();
			$this->processNode($node->expr, $scope, $nodeCallback);
			$scope = $this->lookForAssigns($scope, $node->expr, TrinaryLogic::createYes(), LookForAssignsSettings::default());
			if ($node->keyVar !== null) {
				$this->processNode($node->keyVar, $scope->enterExpressionAssign($node->keyVar), $nodeCallback);
			}
			$this->processNode(
				$node->valueVar,
				$this->lookForEnterVariableAssign($scope, $node->valueVar),
				$nodeCallback
			);

			$scope = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->stmts, false, function (Scope $scope) use ($node): Scope {
					return $this->enterForeach($scope, $node);
				}),
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

			$scopeLoopDefinitelyRan = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->stmts, false, static function (Scope $scope) use ($node): Scope {
					foreach ($node->cond as $condExpr) {
						$scope = $scope->filterByTruthyValue($condExpr);
					}

					return $scope;
				}),
			], LookForAssignsSettings::insideLoop());

			$this->processNodes($node->loop, $scopeLoopDefinitelyRan, $nodeCallback);

			foreach ($node->cond as $condExpr) {
				$scopeLoopMightHaveRun = $this->lookForAssigns($scopeLoopMightHaveRun, $condExpr, TrinaryLogic::createYes(), LookForAssignsSettings::insideLoop());
			}

			foreach ($node->cond as $condExpr) {
				$scopeLoopMightHaveRun = $scopeLoopMightHaveRun->filterByTruthyValue($condExpr);
			}
			$this->processNodes($node->stmts, $scopeLoopMightHaveRun, $nodeCallback);

			return;
		} elseif ($node instanceof While_) {
			$bodyScope = $this->lookForAssigns(
				$scope,
				$node->cond,
				TrinaryLogic::createYes(),
				LookForAssignsSettings::default()
			);
			$condScope = $this->lookForAssignsInBranches($scope, [
				new StatementList($bodyScope, $node->stmts, false, static function (Scope $scope) use ($node): Scope {
					return $scope->filterByTruthyValue($node->cond);
				}),
				new StatementList($scope, []),
			], LookForAssignsSettings::insideLoop());
			$this->processNode($node->cond, $condScope, $nodeCallback);

			$bodyScope = $this->lookForAssignsInBranches($bodyScope, [
				new StatementList($bodyScope, $node->stmts, false, static function (Scope $scope) use ($node): Scope {
					return $scope->filterByTruthyValue($node->cond);
				}),
				new StatementList($bodyScope, []),
			], LookForAssignsSettings::insideLoop());
			$bodyScope = $this->lookForAssigns($bodyScope, $node->cond, TrinaryLogic::createYes(), LookForAssignsSettings::insideLoop());
			$bodyScope = $bodyScope->filterByTruthyValue($node->cond);
			$this->processNodes($node->stmts, $bodyScope, $nodeCallback);
			return;
		} elseif ($node instanceof Catch_) {
			if (!is_string($node->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$scope = $scope->enterCatch(
				$node->types,
				$node->var->name
			);
		} elseif ($node instanceof Array_) {
			$scope = $scope->exitFirstLevelStatements();
			foreach ($node->items as $item) {
				if ($item === null) {
					continue;
				}
				$this->processNode($item, $scope, $nodeCallback);
				if ($item->key !== null) {
					$scope = $this->lookForAssigns($scope, $item->key, TrinaryLogic::createYes(), LookForAssignsSettings::default());
				}
				$scope = $this->lookForAssigns($scope, $item->value, TrinaryLogic::createYes(), LookForAssignsSettings::default());
			}

			return;
		} elseif ($node instanceof Expr\Closure) {
			$this->processNodes($node->uses, $scope, $nodeCallback);
			$usesByRef = [];
			foreach ($node->uses as $closureUse) {
				if (!$closureUse->byRef) {
					continue;
				}

				$usesByRef[] = $closureUse;
			}

			if (count($usesByRef) > 0) {
				$closureScope = $this->lookForAssignsInBranches($scope, [
					new StatementList($scope, $node->stmts),
					new StatementList($scope, []),
				], LookForAssignsSettings::insideClosure());
				/** @var Expr\ClosureUse $closureUse */
				foreach ($usesByRef as $closureUse) {
					if (!is_string($closureUse->var->name)) {
						throw new \PHPStan\ShouldNotHappenException();
					}
					$variableCertainty = $closureScope->hasVariableType($closureUse->var->name);
					if ($variableCertainty->no()) {
						continue;
					}
					$scope = $scope->assignVariable(
						$closureUse->var->name,
						$closureScope->getVariableType($closureUse->var->name),
						$variableCertainty
					);
				}
			}

			$scope = $scope->enterAnonymousFunction($node);
			$this->processNodes($node->stmts, $scope, $nodeCallback);

			return;
		} elseif ($node instanceof If_) {
			$this->processNode($node->cond, $scope->exitFirstLevelStatements(), $nodeCallback);
			$scope = $this->lookForAssigns(
				$scope,
				$node->cond,
				TrinaryLogic::createYes(),
				LookForAssignsSettings::default()
			);
			$ifScope = $scope;
			$scope = $scope->filterByTruthyValue($node->cond);

			$specifyFetchedProperty = function (Node $node, Scope $inScope) use (&$scope): void {
				$this->specifyFetchedPropertyForInnerScope($node, $inScope, false, $scope);
			};
			$this->processNode($node->cond, $scope, $specifyFetchedProperty);
			$this->processNodes($node->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);

			if (count($node->elseifs) > 0 || $node->else !== null) {
				$elseifScope = $ifScope->filterByFalseyValue($node->cond);
				foreach ($node->elseifs as $elseif) {
					$scope = $elseifScope;
					$this->processNode($elseif, $scope, $nodeCallback, true);
					$this->processNode($elseif->cond, $scope->exitFirstLevelStatements(), $nodeCallback);
					$scope = $this->lookForAssigns(
						$scope,
						$elseif->cond,
						TrinaryLogic::createYes(),
						LookForAssignsSettings::default()
					);
					$scope = $scope->filterByTruthyValue($elseif->cond);
					$this->processNode($elseif->cond, $scope, $specifyFetchedProperty);
					$this->processNodes($elseif->stmts, $scope->enterFirstLevelStatements(), $nodeCallback);
					$elseifScope = $this->lookForAssigns(
						$elseifScope,
						$elseif->cond,
						TrinaryLogic::createYes(),
						LookForAssignsSettings::default()
					)->filterByFalseyValue($elseif->cond);
				}
				if ($node->else !== null) {
					$this->processNode($node->else, $elseifScope, $nodeCallback);
				}
			}

			return;
		} elseif ($node instanceof Switch_) {
			$scope = $scope->exitFirstLevelStatements();
			$this->processNode($node->cond, $scope, $nodeCallback);
			$scope = $this->lookForAssigns(
				$scope,
				$node->cond,
				TrinaryLogic::createYes(),
				LookForAssignsSettings::default()
			);
			$switchScope = $scope;
			$switchConditionType = $scope->getType($node->cond)->toBoolean();
			$switchConditionIsTrue = $switchConditionType instanceof ConstantBooleanType && $switchConditionType->getValue();
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
						TrinaryLogic::createYes(),
						LookForAssignsSettings::default()
					);
					$scope = $this->lookForAssigns(
						$scope,
						$caseNode->cond,
						TrinaryLogic::createYes(),
						LookForAssignsSettings::default()
					);

					$caseScope = $switchScope;
					if ($switchConditionIsTrue) {
						$caseScope = $caseScope->filterByTruthyValue($caseNode->cond);
					} elseif (
						$switchConditionGetClassExpression !== null
						&& $caseNode->cond instanceof Expr\ClassConstFetch
						&& $caseNode->cond->class instanceof Name
						&& $caseNode->cond->name instanceof Node\Identifier
						&& strtolower($caseNode->cond->name->name) === 'class'
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
						$switchScope = $this->lookForAssigns($switchScope, $statement, TrinaryLogic::createMaybe(), LookForAssignsSettings::default());
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
				$scope = $this->lookForAssigns($scope, $statement, $tryAssignmentsCertainty, LookForAssignsSettings::default());
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

				if (!is_string($catch->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$statements[] = new StatementList($scope->enterCatch(
					$catch->types,
					$catch->var->name
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
				$node->var instanceof Expr\Closure
				&& $node->name instanceof Node\Identifier
				&& $node->name->name === 'call'
				&& isset($node->args[0])
			) {
				$closureCallScope = $scope->enterClosureCall($scope->getType($node->args[0]->value));
			}
			$scope = $scope->enterFunctionCall($node);
		} elseif ($node instanceof New_ && $node->class instanceof Class_) {
			$this->anonymousClassReflection = $this->broker->getAnonymousClassReflection($node, $scope);
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
					$scope = $scope->enterExpressionAssign($unsetVar);
					if ($unsetVar instanceof StaticPropertyFetch) {
						/** @var Expr $unsetVar */
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
						$scope = $this->ensureNonNullability($scope, $issetVar, true);
					}
				}

				if ($node instanceof MethodCall && $subNodeName === 'args') {
					$scope = $this->lookForAssigns($scope, $node->var, TrinaryLogic::createYes(), LookForAssignsSettings::default());
				}

				if ($node instanceof Do_ && $subNodeName === 'stmts') {
					$scope = $this->lookForAssignsInBranches($scope, [
						new StatementList($scope, $node->stmts),
						new StatementList($scope, [$node->cond], true),
						new StatementList($scope, []),
					], LookForAssignsSettings::insideLoop());
				}

				$this->processNodes($subNode, $scope, $nodeCallback, $argClosureBindScope);
			} elseif ($subNode instanceof \PhpParser\Node) {
				if ($node instanceof Coalesce && $subNodeName === 'left') {
					$scope = $this->ensureNonNullability($scope, $subNode, false);
					$scope = $this->lookForEnterVariableAssign($scope, $node->left);
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

				if ($node instanceof Assign || $node instanceof AssignRef) {
					if ($subNodeName === 'var') {
						$scope = $this->lookForEnterVariableAssign($scope, $node->var);
					} elseif ($subNodeName === 'expr') {
						$scope = $this->lookForEnterVariableAssign($scope, $node);
					}
				}

				if ($node instanceof BinaryOp && $subNodeName === 'right') {
					$scope = $this->lookForAssigns($scope, $node->left, TrinaryLogic::createYes(), LookForAssignsSettings::default());
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
					$scope = $this->lookForAssigns($scope, $node->key, TrinaryLogic::createYes(), LookForAssignsSettings::default());
				}

				if (
					$node instanceof Ternary
					&& $subNodeName !== 'cond'
				) {
					$scope = $this->lookForAssigns($scope, $node->cond, TrinaryLogic::createYes(), LookForAssignsSettings::default());
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
						$scope = $this->lookForAssigns($scope, $statement, TrinaryLogic::createYes(), LookForAssignsSettings::default());
					}
				}

				if ($node instanceof Expr\Empty_ && $subNodeName === 'expr') {
					$scope = $this->ensureNonNullability($scope, $subNode, true);
				}

				if ($node instanceof StaticVar && $subNodeName === 'var') {
					$scope = $scope->enterExpressionAssign($node->var);
				} elseif ($node instanceof Expr\ClosureUse && $subNodeName === 'var') {
					$scope = $scope->enterExpressionAssign($node->var);
				}

				$nodeScope = $scope;
				if (!$node instanceof ErrorSuppress && !$node instanceof Node\Stmt\Expression) {
					$nodeScope = $nodeScope->exitFirstLevelStatements();
				}
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

	private function ensureNonNullability(
		Scope $scope,
		Node $node,
		bool $findMethods
	): Scope
	{
		$nodeToSpecify = $node;
		while (
			$nodeToSpecify instanceof PropertyFetch
			|| $nodeToSpecify instanceof StaticPropertyFetch
			|| (
				$findMethods && (
					$nodeToSpecify instanceof MethodCall
					|| $nodeToSpecify instanceof StaticCall
				)
			)
		) {
			if (
				$nodeToSpecify instanceof PropertyFetch
				|| $nodeToSpecify instanceof MethodCall
			) {
				$nodeToSpecify = $nodeToSpecify->var;
			} elseif ($nodeToSpecify->class instanceof Expr) {
				$nodeToSpecify = $nodeToSpecify->class;
			} else {
				break;
			}

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
		} elseif ($node instanceof AssignRef) {
			$scope = $scope->enterExpressionAssign($node->expr);
		} elseif ($node instanceof Assign && $node->expr instanceof Expr\Closure) {
			foreach ($node->expr->uses as $closureUse) {
				if (
					!$closureUse->byRef
					|| !$node->var instanceof Variable
					|| !is_string($closureUse->var->name)
					|| $node->var->name !== $closureUse->var->name
				) {
					continue;
				}

				$scope = $scope->enterExpressionAssign(new Variable($closureUse->var->name));
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
		LookForAssignsSettings $lookForAssignsSettings
	): Scope
	{
		if ($node instanceof Node\Stmt\Expression) {
			$node = $node->expr;
		}

		if ($node instanceof StaticVar) {
			if (!is_string($node->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$scope = $scope->assignVariable(
				$node->var->name,
				new MixedType(),
				$certainty
			);
		} elseif ($node instanceof Static_) {
			foreach ($node->vars as $var) {
				$scope = $this->lookForAssigns($scope, $var, $certainty, $lookForAssignsSettings);
			}
		} elseif ($node instanceof If_) {
			$conditionType = $scope->getType($node->cond)->toBoolean();
			$scope = $this->lookForAssigns($scope, $node->cond, $certainty, $lookForAssignsSettings);
			$statements = [];

			if (!$conditionType instanceof ConstantBooleanType || $conditionType->getValue() || !$lookForAssignsSettings->skipDeadBranches()) {
				$statements[] = new StatementList(
					$scope,
					array_merge([$node->cond], $node->stmts),
					false,
					static function (Scope $scope) use ($node): Scope {
						return $scope->filterByTruthyValue($node->cond);
					}
				);
			}

			if (!$conditionType instanceof ConstantBooleanType || !$conditionType->getValue() || !$lookForAssignsSettings->skipDeadBranches()) {
				$lastElseIfConditionIsTrue = false;
				$elseIfScope = $scope;
				$lastCond = $node->cond;
				foreach ($node->elseifs as $elseIf) {
					$elseIfScope = $elseIfScope->filterByFalseyValue($lastCond);
					$lastCond = $elseIf->cond;
					$elseIfConditionType = $elseIfScope->getType($elseIf->cond)->toBoolean();
					if (
						$elseIfConditionType instanceof ConstantBooleanType
						&& !$elseIfConditionType->getValue()
						&& $lookForAssignsSettings->skipDeadBranches()
					) {
						break;
					}
					$statements[] = new StatementList(
						$elseIfScope,
						array_merge([$elseIf->cond], $elseIf->stmts),
						false,
						static function (Scope $scope) use ($elseIf): Scope {
							return $scope->filterByTruthyValue($elseIf->cond);
						}
					);
					if (
						$elseIfConditionType instanceof ConstantBooleanType
						&& $elseIfConditionType->getValue()
						&& $lookForAssignsSettings->skipDeadBranches()
					) {
						$lastElseIfConditionIsTrue = true;
						break;
					}
				}

				if (!$lastElseIfConditionIsTrue) {
					$statements[] = new StatementList(
						$elseIfScope,
						$node->else !== null ? $node->else->stmts : [],
						false,
						static function (Scope $scope) use ($lastCond): Scope {
							return $scope->filterByFalseyValue($lastCond);
						}
					);
				}
			}

			$scope = $this->lookForAssignsInBranches($scope, $statements, $lookForAssignsSettings);
		} elseif ($node instanceof TryCatch) {
			$statements = [
				new StatementList($scope, $node->stmts),
			];
			foreach ($node->catches as $catch) {
				if (!is_string($catch->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$statements[] = new StatementList($scope->enterCatch(
					$catch->types,
					$catch->var->name
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
				$scope = $this->lookForAssigns($scope, $node->var, $certainty, $lookForAssignsSettings);
			}
			foreach ($node->args as $argument) {
				$scope = $this->lookForAssigns($scope, $argument, $certainty, $lookForAssignsSettings);
			}

			$parametersAcceptor = $this->findParametersAcceptorInFunctionCall($node, $scope);

			if ($parametersAcceptor !== null) {
				$parameters = $parametersAcceptor->getParameters();
				foreach ($node->args as $i => $arg) {
					$assignByReference = false;
					if (isset($parameters[$i])) {
						$assignByReference = $parameters[$i]->passedByReference()->createsNewVariable();
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

					$scope = $scope->assignVariable($arg->name, new MixedType(), $certainty);
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
				$scope = $scope->assignVariable('http_response_header', new ArrayType(new IntegerType(), new StringType()), $certainty);
			}

			if (
				$node instanceof FuncCall
				&& $node->name instanceof Name
				&& in_array(strtolower((string) $node->name), [
					'array_push',
					'array_unshift',
				], true)
				&& count($node->args) >= 2
			) {
				$argumentTypes = [];
				foreach (array_slice($node->args, 1) as $callArg) {
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

				$arrayArg = $node->args[0]->value;
				$originalArrayType = $scope->getType($arrayArg);
				$functionName = strtolower((string) $node->name);
				$constantArrays = TypeUtils::getConstantArrays($originalArrayType);
				if (
					$functionName === 'array_push'
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
				$node instanceof FuncCall
				&& $node->name instanceof Name
				&& in_array(strtolower((string) $node->name), [
					'array_pop',
					'array_shift',
				], true)
				&& count($node->args) >= 1
			) {
				$arrayArg = $node->args[0]->value;
				$constantArrays = TypeUtils::getConstantArrays($scope->getType($arrayArg));
				$functionName = strtolower((string) $node->name);
				if (count($constantArrays) > 0) {
					$resultArrayTypes = [];

					foreach ($constantArrays as $constantArray) {
						if ($functionName === 'array_pop') {
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
		} elseif ($node instanceof BinaryOp) {
			$scope = $this->lookForAssigns($scope, $node->left, $certainty, $lookForAssignsSettings);
			$scope = $this->lookForAssigns($scope, $node->right, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof Arg) {
			$scope = $this->lookForAssigns($scope, $node->value, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof BooleanNot) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof Ternary) {
			$scope = $this->lookForAssigns($scope, $node->cond, $certainty, $lookForAssignsSettings);
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
					$scope = $this->lookForAssigns($scope, $item->key, $certainty, $lookForAssignsSettings);
				}
				$scope = $this->lookForAssigns($scope, $item->value, $certainty, $lookForAssignsSettings);
			}
		} elseif ($node instanceof New_) {
			foreach ($node->args as $arg) {
				$scope = $this->lookForAssigns($scope, $arg, $certainty, $lookForAssignsSettings);
			}
		} elseif ($node instanceof Do_) {
			$scope = $this->lookForAssignsInBranches($scope, [
				new StatementList($scope, $node->stmts),
			], LookForAssignsSettings::afterLoop());
			$scope = $this->lookForAssigns($scope, $node->cond, TrinaryLogic::createYes(), LookForAssignsSettings::afterLoop());
			$scope = $scope->filterByFalseyValue($node->cond);
		} elseif ($node instanceof Switch_) {
			$scope = $this->lookForAssigns(
				$scope,
				$node->cond,
				TrinaryLogic::createYes(),
				LookForAssignsSettings::default()
			);
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

			$scope = $this->lookForAssignsInBranches($scope, $statementLists, LookForAssignsSettings::afterSwitch());
		} elseif ($node instanceof Cast) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
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
				new StatementList($scope, $node->stmts, false, static function (Scope $scope) use ($node): Scope {
					return $scope->filterByTruthyValue($node->cond);
				}),
				new StatementList($scope, []), // in order not to add variables existing only inside the for loop
			];
			$scope = $this->lookForAssignsInBranches($scope, $statements, LookForAssignsSettings::afterLoop());
		} elseif ($node instanceof ErrorSuppress) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof \PhpParser\Node\Stmt\Unset_) {
			foreach ($node->vars as $var) {
				$scope = $scope->unsetExpression($var);
			}
		} elseif ($node instanceof Echo_) {
			foreach ($node->exprs as $echoedExpr) {
				$scope = $this->lookForAssigns($scope, $echoedExpr, $certainty, $lookForAssignsSettings);
			}
		} elseif ($node instanceof Print_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof Foreach_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
			$statements = [
				new StatementList($scope, array_merge(
					[new Node\Stmt\Nop()],
					$node->stmts
				), false, function (Scope $scope) use ($node): Scope {
					return $this->enterForeach($scope, $node);
				}),
				new StatementList($scope, []), // in order not to add variables existing only inside the for loop
			];
			$scope = $this->lookForAssignsInBranches($scope, $statements, LookForAssignsSettings::afterLoop());
		} elseif ($node instanceof Isset_) {
			foreach ($node->vars as $var) {
				$scope = $this->lookForAssigns($scope, $var, $certainty, $lookForAssignsSettings);
			}
		} elseif ($node instanceof Expr\Empty_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof ArrayDimFetch && $node->dim !== null) {
			$scope = $this->lookForAssigns($scope, $node->dim, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof Expr\Closure) {
			$closureScope = $scope->enterAnonymousFunction($node);
			$statements = [
				new StatementList($closureScope, array_merge(
					[new Node\Stmt\Nop()],
					$node->stmts
				)),
				new StatementList($closureScope, []),
			];
			$closureScope = $this->lookForAssignsInBranches($scope, $statements, LookForAssignsSettings::insideClosure());
			foreach ($node->uses as $closureUse) {
				if (!$closureUse->byRef) {
					continue;
				}

				if (!is_string($closureUse->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$variableCertainty = $closureScope->hasVariableType($closureUse->var->name);
				if ($variableCertainty->no()) {
					continue;
				}

				$scope = $scope->assignVariable(
					$closureUse->var->name,
					$closureScope->getVariableType($closureUse->var->name),
					$variableCertainty
				);
			}
		} elseif ($node instanceof Instanceof_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
		} elseif ($node instanceof Expr\Include_) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
		} elseif (
			(
				$node instanceof Expr\PostInc
				|| $node instanceof Expr\PostDec
				|| $node instanceof Expr\PreInc
				|| $node instanceof Expr\PreDec
			) && (
				$node->var instanceof Variable
				|| $node->var instanceof ArrayDimFetch
				|| $node->var instanceof PropertyFetch
				|| $node->var instanceof StaticPropertyFetch
			)
		) {
			$expressionType = $scope->getType($node->var);
			if ($expressionType instanceof ConstantScalarType) {
				$afterValue = $expressionType->getValue();
				if (
					$node instanceof Expr\PostInc
					|| $node instanceof Expr\PreInc
				) {
					$afterValue++;
				} else {
					$afterValue--;
				}

				$newExpressionType = $scope->getTypeFromValue($afterValue);
				if ($lookForAssignsSettings->shouldGeneralizeConstantTypesOfNonIdempotentOperations()) {
					$newExpressionType = TypeUtils::generalizeType($newExpressionType);
				}

				$scope = $this->assignVariable(
					$scope,
					$node->var,
					$certainty,
					$newExpressionType
				);
			}
		} elseif ($node instanceof Expr\Yield_) {
			if ($node->key !== null) {
				$scope = $this->lookForAssigns($scope, $node->key, $certainty, $lookForAssignsSettings);
			}
			if ($node->value !== null) {
				$scope = $this->lookForAssigns($scope, $node->value, $certainty, $lookForAssignsSettings);
			}
		} elseif ($node instanceof Expr\YieldFrom) {
			$scope = $this->lookForAssigns($scope, $node->expr, $certainty, $lookForAssignsSettings);
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
				$scope = $this->lookForAssigns($scope, $node->var, TrinaryLogic::createYes(), $lookForAssignsSettings);
				$vars = [$node->var];
			} else {
				$vars = $node->vars;
			}

			foreach ($vars as $var) {
				if (
					!$var instanceof Variable
					&& !$var instanceof ArrayDimFetch
					&& !$var instanceof PropertyFetch
					&& !$var instanceof StaticPropertyFetch
				) {
					continue;
				}

				$type = null;
				if ($node instanceof Assign || $node instanceof AssignRef) {
					$type = $scope->getType($node->expr);
				} elseif ($node instanceof Expr\AssignOp) {
					$type = $scope->getType($node);
					if ($lookForAssignsSettings->shouldGeneralizeConstantTypesOfNonIdempotentOperations()) {
						$type = TypeUtils::generalizeType($type);
					}
				}

				$scope = $this->assignVariable($scope, $var, $certainty, $type);
				if (
					(!$node instanceof Assign && !$node instanceof AssignRef)
					|| !$lookForAssignsSettings->shouldGeneralizeConstantTypesOfNonIdempotentOperations()
					|| !($var instanceof ArrayDimFetch)
					|| $var->dim !== null
				) {
					continue;
				}

				$type = $scope->getType($var->var);
				$scope = $this->assignVariable($scope, $var->var, $certainty, TypeUtils::generalizeType($type));
			}

			if ($node instanceof Assign || $node instanceof AssignRef) {
				if ($node->var instanceof Array_ || $node->var instanceof List_) {
					$scope = $this->lookForArrayDestructuringArray($scope, $node->var, $scope->getType($node->expr));
				}
			}

			if (!$node instanceof Node\Stmt\Global_) {
				$scope = $this->lookForAssigns($scope, $node->expr, TrinaryLogic::createYes(), $lookForAssignsSettings);
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
		?Type $subNodeType = null
	): Scope
	{
		if ($var instanceof Variable && is_string($var->name)) {
			$scope = $scope->assignVariable($var->name, $subNodeType ?? new MixedType(), $certainty);
		} elseif ($var instanceof ArrayDimFetch) {
			$subNodeType = $subNodeType ?? new MixedType();

			$dimExprStack = [];
			while ($var instanceof ArrayDimFetch) {
				$dimExprStack[] = $var->dim;
				$var = $var->var;
			}

			// 1. eval root expr
			$scope = $this->lookForAssigns($scope, $var, TrinaryLogic::createYes(), LookForAssignsSettings::default());

			// 2. eval dimensions
			$offsetTypes = [];
			foreach (array_reverse($dimExprStack) as $dimExpr) {
				if ($dimExpr === null) {
					$offsetTypes[] = null;

				} else {
					$scope = $this->lookForAssigns($scope, $dimExpr, TrinaryLogic::createYes(), LookForAssignsSettings::default());
					$offsetTypes[] = $scope->getType($dimExpr);
				}
			}

			// 3. eval assigned expr, unfortunately this was already done

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

			$valueToWrite = $subNodeType;
			foreach (array_reverse($offsetTypes) as $offsetType) {
				/** @var Type $offsetValueType */
				$offsetValueType = array_pop($offsetValueTypeStack);
				$valueToWrite = $offsetValueType->setOffsetValueType($offsetType, $valueToWrite);
			}

			if ($valueToWrite instanceof ErrorType) {
				$valueToWrite = new ArrayType(new MixedType(), new MixedType());
			}

			if ($var instanceof Variable && is_string($var->name)) {
				$scope = $scope->assignVariable(
					$var->name,
					$valueToWrite,
					$certainty
				);
			} else {
				$scope = $scope->specifyExpressionType(
					$var,
					$valueToWrite
				);
			}
		} elseif ($var instanceof PropertyFetch && $subNodeType !== null) {
			$scope = $scope->specifyExpressionType($var, $subNodeType);
		} elseif ($var instanceof Expr\StaticPropertyFetch && $subNodeType !== null) {
			$scope = $scope->specifyExpressionType($var, $subNodeType);
		} else {
			$scope = $this->lookForAssigns($scope, $var, TrinaryLogic::createYes(), LookForAssignsSettings::default());
		}

		return $scope;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $initialScope
	 * @param \PHPStan\Analyser\StatementList[] $statementsLists
	 * @param \PHPStan\Analyser\LookForAssignsSettings $lookForAssignsSettings
	 * @param int $counter
	 * @return Scope
	 */
	private function lookForAssignsInBranches(
		Scope $initialScope,
		array $statementsLists,
		LookForAssignsSettings $lookForAssignsSettings,
		int $counter = 0
	): Scope
	{
		/** @var \PHPStan\Analyser\Scope|null $intersectedScope */
		$intersectedScope = null;
		foreach ($statementsLists as $statementList) {
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
					$branchScopeWithInitialScopeRemoved = $branchScopeWithInitialScopeRemoved->removeSpecified($initialScope);
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
			if ($counter === 0 && $lookForAssignsSettings->skipDeadBranches()) {
				$newStatementLists = [];
				foreach ($statementsLists as $statementList) {
					$newStatementLists[] = StatementList::fromList(
						$scope,
						$statementList
					);
				}
				return $this->lookForAssignsInBranches(
					$scope,
					$newStatementLists,
					$lookForAssignsSettings,
					$counter + 1
				);
			}

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
		if ($statement instanceof Node\Stmt\Expression) {
			$statement = $statement->expr;
		}

		if (
			$statement instanceof Throw_
			|| $statement instanceof Return_
			|| $statement instanceof Continue_
			|| $statement instanceof Break_
			|| $statement instanceof Exit_
		) {
			return $statement;
		} elseif (($statement instanceof MethodCall || $statement instanceof Expr\StaticCall) && count($this->earlyTerminatingMethodCalls) > 0) {
			if ($statement->name instanceof Expr) {
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

					if (in_array((string) $statement->name, $this->earlyTerminatingMethodCalls[$className], true)) {
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
				return ParametersAcceptorSelector::selectFromArgs(
					$scope,
					$functionCall->args,
					$this->broker->getFunction($functionCall->name, $scope)->getVariants()
				);
			}
		} elseif ($functionCall instanceof MethodCall && $functionCall->name instanceof Node\Identifier) {
			$type = $scope->getType($functionCall->var);
			$methodName = $functionCall->name->name;
			if ($type->hasMethod($methodName)) {
				return ParametersAcceptorSelector::selectFromArgs(
					$scope,
					$functionCall->args,
					$type->getMethod($methodName, $scope)->getVariants()
				);
			}
		} elseif (
			$functionCall instanceof Expr\StaticCall
			&& $functionCall->class instanceof Name
			&& $functionCall->name instanceof Node\Identifier
		) {
			$className = $scope->resolveName($functionCall->class);
			if ($this->broker->hasClass($className)) {
				$classReflection = $this->broker->getClass($className);
				if ($classReflection->hasMethod($functionCall->name->name)) {
					return ParametersAcceptorSelector::selectFromArgs(
						$scope,
						$functionCall->args,
						$classReflection->getMethod($functionCall->name->name, $scope)->getVariants()
					);
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
	 * @param \Closure(\PhpParser\Node $node): void $nodeCallback
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
		[$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $isDeprecated, $isInternal, $isFinal] = $this->getPhpDocs($scope, $classMethod);

		return $scope->enterClassMethod(
			$classMethod,
			$phpDocParameterTypes,
			$phpDocReturnType,
			$phpDocThrowType,
			$isDeprecated,
			$isInternal,
			$isFinal
		);
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
					$functionLike->name->name,
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
			$phpDocParameterTypes = array_map(static function (ParamTag $tag): Type {
				return $tag->getType();
			}, $resolvedPhpDoc->getParamTags());
			$phpDocReturnType = $resolvedPhpDoc->getReturnTag() !== null ? $resolvedPhpDoc->getReturnTag()->getType() : null;
			$phpDocThrowType = $resolvedPhpDoc->getThrowsTag() !== null ? $resolvedPhpDoc->getThrowsTag()->getType() : null;
			$isDeprecated = $resolvedPhpDoc->isDeprecated();
			$isInternal = $resolvedPhpDoc->isInternal();
			$isFinal = $resolvedPhpDoc->isFinal();
		}

		return [$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $isDeprecated, $isInternal, $isFinal];
	}

	private function enterFunction(Scope $scope, Node\Stmt\Function_ $function): Scope
	{
		[$phpDocParameterTypes, $phpDocReturnType, $phpDocThrowType, $isDeprecated, $isInternal, $isFinal] = $this->getPhpDocs($scope, $function);

		return $scope->enterFunction(
			$function,
			$phpDocParameterTypes,
			$phpDocReturnType,
			$phpDocThrowType,
			$isDeprecated,
			$isInternal,
			$isFinal
		);
	}

}
