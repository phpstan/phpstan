<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\Unset_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;

class Scope implements ClassMemberAccessAnswerer
{

	/** @var \PHPStan\Analyser\ScopeFactory */
	private $scopeFactory;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	/** @var \PHPStan\Analyser\ScopeContext */
	private $context;

	/** @var \PHPStan\Type\Type[] */
	private $resolvedTypes = [];

	/** @var bool */
	private $declareStrictTypes;

	/** @var \PHPStan\Reflection\FunctionReflection|MethodReflection|null */
	private $function;

	/** @var string|null */
	private $namespace;

	/** @var \PHPStan\Analyser\VariableTypeHolder[] */
	private $variableTypes;

	/** @var \PHPStan\Analyser\VariableTypeHolder[] */
	private $moreSpecificTypes;

	/** @var string|null */
	private $inClosureBindScopeClass;

	/** @var \PHPStan\Type\Type|null */
	private $inAnonymousFunctionReturnType;

	/** @var \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null */
	private $inFunctionCall;

	/** @var bool */
	private $negated;

	/** @var bool */
	private $inFirstLevelStatement;

	/** @var string[] */
	private $currentlyAssignedExpressions = [];

	/** @var string[] */
	private $dynamicConstantNames;

	/**
	 * @param  \PHPStan\Analyser\ScopeFactory $scopeFactory
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Analyser\TypeSpecifier $typeSpecifier
	 * @param \PHPStan\Analyser\ScopeContext $context
	 * @param bool $declareStrictTypes
	 * @param \PHPStan\Reflection\FunctionReflection|MethodReflection|null $function
	 * @param string|null $namespace
	 * @param \PHPStan\Analyser\VariableTypeHolder[] $variablesTypes
	 * @param \PHPStan\Analyser\VariableTypeHolder[] $moreSpecificTypes
	 * @param string|null $inClosureBindScopeClass
	 * @param \PHPStan\Type\Type|null $inAnonymousFunctionReturnType
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null $inFunctionCall
	 * @param bool $negated
	 * @param bool $inFirstLevelStatement
	 * @param string[] $currentlyAssignedExpressions
	 * @param string[] $dynamicConstantNames
	 */
	public function __construct(
		ScopeFactory $scopeFactory,
		Broker $broker,
		\PhpParser\PrettyPrinter\Standard $printer,
		TypeSpecifier $typeSpecifier,
		ScopeContext $context,
		bool $declareStrictTypes = false,
		$function = null,
		?string $namespace = null,
		array $variablesTypes = [],
		array $moreSpecificTypes = [],
		?string $inClosureBindScopeClass = null,
		?Type $inAnonymousFunctionReturnType = null,
		?Expr $inFunctionCall = null,
		bool $negated = false,
		bool $inFirstLevelStatement = true,
		array $currentlyAssignedExpressions = [],
		array $dynamicConstantNames = []
	)
	{
		if ($namespace === '') {
			$namespace = null;
		}

		$this->scopeFactory = $scopeFactory;
		$this->broker = $broker;
		$this->printer = $printer;
		$this->typeSpecifier = $typeSpecifier;
		$this->context = $context;
		$this->declareStrictTypes = $declareStrictTypes;
		$this->function = $function;
		$this->namespace = $namespace;
		$this->variableTypes = $variablesTypes;
		$this->moreSpecificTypes = $moreSpecificTypes;
		$this->inClosureBindScopeClass = $inClosureBindScopeClass;
		$this->inAnonymousFunctionReturnType = $inAnonymousFunctionReturnType;
		$this->inFunctionCall = $inFunctionCall;
		$this->negated = $negated;
		$this->inFirstLevelStatement = $inFirstLevelStatement;
		$this->currentlyAssignedExpressions = $currentlyAssignedExpressions;
		$this->dynamicConstantNames = $dynamicConstantNames;
	}

	public function getFile(): string
	{
		return $this->context->getFile();
	}

	public function getFileDescription(): string
	{
		if ($this->context->getTraitReflection() === null) {
			return $this->getFile();
		}

		/** @var ClassReflection $classReflection */
		$classReflection = $this->context->getClassReflection();

		$className = sprintf('class %s', $classReflection->getDisplayName());
		if ($classReflection->isAnonymous()) {
			$className = 'anonymous class';
		}

		$traitReflection = $this->context->getTraitReflection();
		if ($traitReflection->getFileName() === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return sprintf(
			'%s (in context of %s)',
			$traitReflection->getFileName(),
			$className
		);
	}

	public function isDeclareStrictTypes(): bool
	{
		return $this->declareStrictTypes;
	}

	public function enterDeclareStrictTypes(): self
	{
		return $this->scopeFactory->create(
			$this->context,
			true
		);
	}

	public function isInClass(): bool
	{
		return $this->context->getClassReflection() !== null;
	}

	public function isInTrait(): bool
	{
		return $this->context->getTraitReflection() !== null;
	}

	public function getClassReflection(): ?ClassReflection
	{
		return $this->context->getClassReflection();
	}

	public function getTraitReflection(): ?ClassReflection
	{
		return $this->context->getTraitReflection();
	}

	/**
	 * @return \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null
	 */
	public function getFunction()
	{
		return $this->function;
	}

	public function getFunctionName(): ?string
	{
		return $this->function !== null ? $this->function->getName() : null;
	}

	public function getNamespace(): ?string
	{
		return $this->namespace;
	}

	/**
	 * @return array<string, \PHPStan\Analyser\VariableTypeHolder>
	 */
	private function getVariableTypes(): array
	{
		return $this->variableTypes;
	}

	public function hasVariableType(string $variableName): TrinaryLogic
	{
		if ($this->isGlobalVariable($variableName)) {
			return TrinaryLogic::createYes();
		}

		if (!isset($this->variableTypes[$variableName])) {
			return TrinaryLogic::createNo();
		}

		return $this->variableTypes[$variableName]->getCertainty();
	}

	public function getVariableType(string $variableName): Type
	{
		if ($this->isGlobalVariable($variableName)) {
			return new ArrayType(new StringType(), new MixedType());
		}

		if ($this->hasVariableType($variableName)->no()) {
			throw new \PHPStan\Analyser\UndefinedVariableException($this, $variableName);
		}

		return $this->variableTypes[$variableName]->getType();
	}

	private function isGlobalVariable(string $variableName): bool
	{
		return in_array($variableName, [
			'GLOBALS',
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_COOKIE',
			'_SESSION',
			'_REQUEST',
			'_ENV',
		], true);
	}

	public function hasConstant(Name $name): bool
	{
		$node = new ConstFetch(new Name\FullyQualified($name->toString()));
		if ($this->isSpecified($node)) {
			return true;
		}
		return $this->broker->hasConstant($name, $this);
	}

	public function isInAnonymousFunction(): bool
	{
		return $this->inAnonymousFunctionReturnType !== null;
	}

	public function getAnonymousFunctionReturnType(): ?\PHPStan\Type\Type
	{
		return $this->inAnonymousFunctionReturnType;
	}

	/**
	 * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
	 */
	public function getInFunctionCall()
	{
		return $this->inFunctionCall;
	}

	public function getType(Expr $node): Type
	{
		$key = $this->printer->prettyPrintExpr($node);
		if (!array_key_exists($key, $this->resolvedTypes)) {
			$this->resolvedTypes[$key] = $this->resolveType($node);
		}
		return $this->resolvedTypes[$key];
	}

	private function resolveType(Expr $node): Type
	{
		if (
			$node instanceof Expr\BinaryOp\Greater
			|| $node instanceof Expr\BinaryOp\GreaterOrEqual
			|| $node instanceof Expr\BinaryOp\Smaller
			|| $node instanceof Expr\BinaryOp\SmallerOrEqual
			|| $node instanceof Expr\BinaryOp\Equal
			|| $node instanceof Expr\BinaryOp\NotEqual
			|| $node instanceof Expr\Empty_
		) {
			return new BooleanType();
		}

		if ($node instanceof Expr\Isset_) {
			foreach ($node->vars as $var) {
				if ($var instanceof Expr\ArrayDimFetch && $var->dim !== null) {
					$hasOffset = $this->getType($var->var)->hasOffsetValueType(
						$this->getType($var->dim)
					)->toBooleanType();
					if ($hasOffset instanceof ConstantBooleanType) {
						if (!$hasOffset->getValue()) {
							return $hasOffset;
						}

						continue;
					}

					return $hasOffset;
				}

				return new BooleanType();
			}

			return new ConstantBooleanType(true);
		}

		if ($node instanceof \PhpParser\Node\Expr\BooleanNot) {
			$exprBooleanType = $this->getType($node->expr)->toBoolean();
			if ($exprBooleanType instanceof ConstantBooleanType) {
				return new ConstantBooleanType(!$exprBooleanType->getValue());
			}

			return new BooleanType();
		}

		if (
			$node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalAnd
		) {
			$leftBooleanType = $this->getType($node->left)->toBoolean();
			if (
				$leftBooleanType instanceof ConstantBooleanType
				&& !$leftBooleanType->getValue()
			) {
				return new ConstantBooleanType(false);
			}

			$rightBooleanType = $this->filterByTruthyValue($node->left)->getType($node->right)->toBoolean();
			if (
				$rightBooleanType instanceof ConstantBooleanType
				&& !$rightBooleanType->getValue()
			) {
				return new ConstantBooleanType(false);
			}

			if (
				$leftBooleanType instanceof ConstantBooleanType
				&& $leftBooleanType->getValue()
				&& $rightBooleanType instanceof ConstantBooleanType
				&& $rightBooleanType->getValue()
			) {
				return new ConstantBooleanType(true);
			}

			return new BooleanType();
		}

		if (
			$node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalOr
		) {
			$leftBooleanType = $this->getType($node->left)->toBoolean();
			if (
				$leftBooleanType instanceof ConstantBooleanType
				&& $leftBooleanType->getValue()
			) {
				return new ConstantBooleanType(true);
			}

			$rightBooleanType = $this->filterByFalseyValue($node->left)->getType($node->right)->toBoolean();
			if (
				$rightBooleanType instanceof ConstantBooleanType
				&& $rightBooleanType->getValue()
			) {
				return new ConstantBooleanType(true);
			}

			if (
				$leftBooleanType instanceof ConstantBooleanType
				&& !$leftBooleanType->getValue()
				&& $rightBooleanType instanceof ConstantBooleanType
				&& !$rightBooleanType->getValue()
			) {
				return new ConstantBooleanType(false);
			}

			return new BooleanType();
		}

		if ($node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalXor) {
			$leftBooleanType = $this->getType($node->left)->toBoolean();
			$rightBooleanType = $this->filterByFalseyValue($node->left)->getType($node->right)->toBoolean();
			if (
				$leftBooleanType instanceof ConstantBooleanType
				&& $rightBooleanType instanceof ConstantBooleanType
			) {
				return new ConstantBooleanType(
					$leftBooleanType->getValue() xor $rightBooleanType->getValue()
				);
			}

			return new BooleanType();
		}

		if ($node instanceof Expr\BinaryOp\Identical) {
			$leftType = $this->getType($node->left);
			$rightType = $this->getType($node->right);

			if (
				(
					$node->left instanceof Node\Expr\PropertyFetch
					|| $node->left instanceof Node\Expr\StaticPropertyFetch
				)
				&& $rightType instanceof NullType
			) {
				return new BooleanType();
			}

			if (
				(
					$node->right instanceof Node\Expr\PropertyFetch
					|| $node->right instanceof Node\Expr\StaticPropertyFetch
				)
				&& $leftType instanceof NullType
			) {
				return new BooleanType();
			}

			$isSuperset = $leftType->isSuperTypeOf($rightType);
			if ($isSuperset->no()) {
				return new ConstantBooleanType(false);
			} elseif (
				$isSuperset->yes()
				&& $leftType instanceof ConstantScalarType
				&& $rightType instanceof ConstantScalarType
				&& $leftType->getValue() === $rightType->getValue()
			) {
				return new ConstantBooleanType(true);
			}

			return new BooleanType();
		}

		if ($node instanceof Expr\BinaryOp\NotIdentical) {
			$leftType = $this->getType($node->left);
			$rightType = $this->getType($node->right);

			if (
				(
					$node->left instanceof Node\Expr\PropertyFetch
					|| $node->left instanceof Node\Expr\StaticPropertyFetch
				)
				&& $rightType instanceof NullType
			) {
				return new BooleanType();
			}

			if (
				(
					$node->right instanceof Node\Expr\PropertyFetch
					|| $node->right instanceof Node\Expr\StaticPropertyFetch
				)
				&& $leftType instanceof NullType
			) {
				return new BooleanType();
			}

			$isSuperset = $leftType->isSuperTypeOf($rightType);
			if ($isSuperset->no()) {
				return new ConstantBooleanType(true);
			} elseif (
				$isSuperset->yes()
				&& $leftType instanceof ConstantScalarType
				&& $rightType instanceof ConstantScalarType
				&& $leftType->getValue() === $rightType->getValue()
			) {
				return new ConstantBooleanType(false);
			}

			return new BooleanType();
		}

		if ($node instanceof Expr\Instanceof_) {
			if ($node->class instanceof Node\Name) {
				$className = $this->resolveName($node->class);
				$type = new ObjectType($className);
			} else {
				$type = $this->getType($node->class);
			}

			$expressionType = $this->getType($node->expr);
			if (
				$this->isInTrait()
				&& TypeUtils::findThisType($expressionType) !== null
			) {
				return new BooleanType();
			}
			if ($expressionType instanceof NeverType) {
				return new ConstantBooleanType(false);
			}
			$isExpressionObject = (new ObjectWithoutClassType())->isSuperTypeOf($expressionType);
			if (!$isExpressionObject->no() && !(new StringType())->isSuperTypeOf($type)->no()) {
				return new BooleanType();
			}

			$isSuperType = $type->isSuperTypeOf($expressionType)
				->and($isExpressionObject);
			if ($isSuperType->no()) {
				return new ConstantBooleanType(false);
			} elseif ($isSuperType->yes()) {
				return new ConstantBooleanType(true);
			}

			return new BooleanType();
		}

		if ($node instanceof Node\Expr\UnaryPlus) {
			return $this->getType($node->expr)->toNumber();
		}

		if ($node instanceof Expr\ErrorSuppress
			|| $node instanceof Expr\Assign
		) {
			return $this->getType($node->expr);
		}

		if ($node instanceof Node\Expr\UnaryMinus) {
			$type = $this->getType($node->expr)->toNumber();
			$scalarValues = TypeUtils::getConstantScalars($type);
			if (count($scalarValues) > 0) {
				$newTypes = [];
				foreach ($scalarValues as $scalarValue) {
					if ($scalarValue instanceof ConstantIntegerType) {
						$newTypes[] = new ConstantIntegerType(-$scalarValue->getValue());
					} elseif ($scalarValue instanceof ConstantFloatType) {
						$newTypes[] = new ConstantFloatType(-$scalarValue->getValue());
					}
				}

				return TypeCombinator::union(...$newTypes);
			}

			return $type;
		}

		if ($node instanceof Expr\BinaryOp\Concat || $node instanceof Expr\AssignOp\Concat) {
			if ($node instanceof Node\Expr\AssignOp) {
				$left = $node->var;
				$right = $node->expr;
			} else {
				$left = $node->left;
				$right = $node->right;
			}

			$leftStringType = $this->getType($left)->toString();
			$rightStringType = $this->getType($right)->toString();
			if (TypeCombinator::union(
				$leftStringType,
				$rightStringType
			) instanceof ErrorType) {
				return new ErrorType();
			}

			if ($leftStringType instanceof ConstantStringType && $rightStringType instanceof ConstantStringType) {
				return $leftStringType->append($rightStringType);
			}

			return new StringType();
		}

		if (
			$node instanceof Node\Expr\BinaryOp\Div
			|| $node instanceof Node\Expr\AssignOp\Div
			|| $node instanceof Node\Expr\BinaryOp\Mod
			|| $node instanceof Node\Expr\AssignOp\Mod
		) {
			if ($node instanceof Node\Expr\AssignOp) {
				$right = $node->expr;
			} else {
				$right = $node->right;
			}

			$rightTypes = TypeUtils::getConstantScalars($this->getType($right)->toNumber());
			foreach ($rightTypes as $rightType) {
				if (
					$rightType->getValue() === 0
					|| $rightType->getValue() === 0.0
				) {
					return new ErrorType();
				}
			}
		}

		if (
			(
				$node instanceof Node\Expr\BinaryOp
				|| $node instanceof Node\Expr\AssignOp
			) && !$node instanceof Expr\BinaryOp\Coalesce
		) {
			if ($node instanceof Node\Expr\AssignOp) {
				$left = $node->var;
				$right = $node->expr;
			} else {
				$left = $node->left;
				$right = $node->right;
			}

			$leftTypes = TypeUtils::getConstantScalars($this->getType($left));
			$rightTypes = TypeUtils::getConstantScalars($this->getType($right));

			if (count($leftTypes) > 0 && count($rightTypes) > 0) {
				$resultTypes = [];
				foreach ($leftTypes as $leftType) {
					foreach ($rightTypes as $rightType) {
						$resultTypes[] = $this->calculateFromScalars($node, $leftType, $rightType);
					}
				}
				return TypeCombinator::union(...$resultTypes);
			}
		}

		if ($node instanceof Node\Expr\BinaryOp\Mod || $node instanceof Expr\AssignOp\Mod) {
			return new IntegerType();
		}

		if ($node instanceof Expr\BinaryOp\Spaceship) {
			return new IntegerType();
		}

		if ($node instanceof Expr\BinaryOp\Coalesce) {
			if ($node->left instanceof Expr\ArrayDimFetch && $node->left->dim !== null) {
				$dimType = $this->getType($node->left->dim);
				$varType = $this->getType($node->left->var);
				$hasOffset = $varType->hasOffsetValueType($dimType);
				$leftType = $this->getType($node->left);
				$rightType = $this->getType($node->right);
				if ($hasOffset->no()) {
					return $rightType;
				} elseif ($hasOffset->yes()) {
					$offsetValueType = $varType->getOffsetValueType($dimType);
					if ($offsetValueType->isSuperTypeOf(new NullType())->no()) {
						return TypeCombinator::removeNull($leftType);
					}
				}

				return TypeCombinator::union(
					TypeCombinator::removeNull($leftType),
					$rightType
				);
			}

			$leftType = $this->getType($node->left);
			$rightType = $this->getType($node->right);
			if ($leftType instanceof ErrorType || $leftType instanceof NullType) {
				return $rightType;
			}

			if (TypeCombinator::containsNull($leftType) || $node->left instanceof PropertyFetch) {
				return TypeCombinator::union(
					TypeCombinator::removeNull($leftType),
					$rightType
				);
			}

			return TypeCombinator::removeNull($leftType);
		}

		if ($node instanceof Expr\Clone_) {
			return $this->getType($node->expr);
		}

		if (
			$node instanceof Expr\AssignOp\ShiftLeft
			|| $node instanceof Expr\BinaryOp\ShiftLeft
			|| $node instanceof Expr\AssignOp\ShiftRight
			|| $node instanceof Expr\BinaryOp\ShiftRight
			|| $node instanceof Expr\AssignOp\BitwiseAnd
			|| $node instanceof Expr\BinaryOp\BitwiseAnd
			|| $node instanceof Expr\AssignOp\BitwiseOr
			|| $node instanceof Expr\BinaryOp\BitwiseOr
			|| $node instanceof Expr\AssignOp\BitwiseXor
			|| $node instanceof Expr\BinaryOp\BitwiseXor
		) {
			if ($node instanceof Node\Expr\AssignOp) {
				$left = $node->var;
				$right = $node->expr;
			} else {
				$left = $node->left;
				$right = $node->right;
			}

			if (TypeCombinator::union(
				$this->getType($left)->toNumber(),
				$this->getType($right)->toNumber()
			) instanceof ErrorType) {
				return new ErrorType();
			}

			return new IntegerType();
		}

		if (
			$node instanceof Node\Expr\BinaryOp\Plus
			|| $node instanceof Node\Expr\BinaryOp\Minus
			|| $node instanceof Node\Expr\BinaryOp\Mul
			|| $node instanceof Node\Expr\BinaryOp\Pow
			|| $node instanceof Node\Expr\BinaryOp\Div
			|| $node instanceof Node\Expr\AssignOp\Plus
			|| $node instanceof Node\Expr\AssignOp\Minus
			|| $node instanceof Node\Expr\AssignOp\Mul
			|| $node instanceof Node\Expr\AssignOp\Pow
			|| $node instanceof Node\Expr\AssignOp\Div
		) {
			if ($node instanceof Node\Expr\AssignOp) {
				$left = $node->var;
				$right = $node->expr;
			} else {
				$left = $node->left;
				$right = $node->right;
			}

			$leftType = $this->getType($left);
			$rightType = $this->getType($right);

			if ($node instanceof Expr\AssignOp\Plus || $node instanceof Expr\BinaryOp\Plus) {
				$leftConstantArrays = TypeUtils::getConstantArrays($leftType);
				$rightConstantArrays = TypeUtils::getConstantArrays($rightType);

				if (count($leftConstantArrays) > 0 && count($rightConstantArrays) > 0) {
					$resultTypes = [];
					foreach ($rightConstantArrays as $rightConstantArray) {
						foreach ($leftConstantArrays as $leftConstantArray) {
							$newArrayBuilder = ConstantArrayTypeBuilder::createFromConstantArray($rightConstantArray);
							foreach ($leftConstantArray->getKeyTypes() as $leftKeyType) {
								$newArrayBuilder->setOffsetValueType(
									$leftKeyType,
									$leftConstantArray->getOffsetValueType($leftKeyType)
								);
							}
							$resultTypes[] = $newArrayBuilder->getArray();
						}
					}

					return TypeCombinator::union(...$resultTypes);
				}

				$leftArrays = TypeUtils::getArrays($leftType);
				$rightArrays = TypeUtils::getArrays($rightType);

				if (count($leftArrays) > 0 && count($rightArrays) > 0) {
					$resultTypes = [];
					foreach ($rightArrays as $rightArray) {
						foreach ($leftArrays as $leftArray) {
							$resultTypes[] = new ArrayType(
								TypeCombinator::union($leftArray->getKeyType(), $rightArray->getKeyType()),
								TypeCombinator::union($leftArray->getItemType(), $rightArray->getItemType())
							);
						}
					}

					return TypeCombinator::union(...$resultTypes);
				}

				if ($leftType instanceof MixedType && $rightType instanceof MixedType) {
					return new BenevolentUnionType([
						new FloatType(),
						new IntegerType(),
						new ArrayType(new MixedType(), new MixedType()),
					]);
				}
			}

			$types = TypeCombinator::union($leftType, $rightType);
			if (
				$leftType instanceof ArrayType
				|| $rightType instanceof ArrayType
				|| $types instanceof ArrayType
			) {
				return new ErrorType();
			}

			$leftNumberType = $leftType->toNumber();
			$rightNumberType = $rightType->toNumber();

			if (
				(new FloatType())->isSuperTypeOf($leftNumberType)->yes()
				|| (new FloatType())->isSuperTypeOf($rightNumberType)->yes()
			) {
				return new FloatType();
			}

			if ($node instanceof Expr\AssignOp\Pow || $node instanceof Expr\BinaryOp\Pow) {
				return new BenevolentUnionType([
					new FloatType(),
					new IntegerType(),
				]);
			}

			$resultType = TypeCombinator::union($leftNumberType, $rightNumberType);
			if ($node instanceof Expr\AssignOp\Div || $node instanceof Expr\BinaryOp\Div) {
				if ($types instanceof MixedType || $resultType instanceof IntegerType) {
					return new BenevolentUnionType([new IntegerType(), new FloatType()]);
				}

				return new UnionType([new IntegerType(), new FloatType()]);
			}

			if ($types instanceof MixedType) {
				return TypeUtils::toBenevolentUnion($resultType);
			}

			return $resultType;
		}

		if ($node instanceof LNumber) {
			return new ConstantIntegerType($node->value);
		} elseif ($node instanceof String_) {
			return new ConstantStringType($node->value);
		} elseif ($node instanceof Node\Scalar\Encapsed) {
			$constantString = new ConstantStringType('');
			foreach ($node->parts as $part) {
				if ($part instanceof EncapsedStringPart) {
					$partStringType = new ConstantStringType($part->value);
				} else {
					$partStringType = $this->getType($part)->toString();
					if ($partStringType instanceof ErrorType) {
						return new ErrorType();
					}
					if (!$partStringType instanceof ConstantStringType) {
						return new StringType();
					}
				}

				$constantString = $constantString->append($partStringType);
			}
			return $constantString;
		} elseif ($node instanceof DNumber) {
			return new ConstantFloatType($node->value);
		} elseif ($node instanceof Expr\Closure) {
			$parameters = [];
			$isVariadic = false;
			$firstOptionalParameterIndex = null;
			foreach ($node->params as $i => $param) {
				$isOptionalCandidate = $param->default !== null || $param->variadic;

				if ($isOptionalCandidate) {
					if ($firstOptionalParameterIndex === null) {
						$firstOptionalParameterIndex = $i;
					}
				} else {
					$firstOptionalParameterIndex = null;
				}
			}

			foreach ($node->params as $i => $param) {
				if ($param->variadic) {
					$isVariadic = true;
				}
				if (!$param->var instanceof Variable || !is_string($param->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$parameters[] = new NativeParameterReflection(
					$param->var->name,
					$firstOptionalParameterIndex !== null && $i >= $firstOptionalParameterIndex,
					$this->getFunctionType($param->type, $param->type === null, false),
					$param->byRef
						? PassedByReference::createCreatesNewVariable()
						: PassedByReference::createNo(),
					$param->variadic
				);
			}

			return new ClosureType(
				$parameters,
				$this->getFunctionType($node->returnType, $node->returnType === null, false),
				$isVariadic
			);
		} elseif ($node instanceof New_) {
			if ($node->class instanceof Name) {
				if (
					count($node->class->parts) === 1
				) {
					$lowercasedClassName = strtolower($node->class->parts[0]);
					if (in_array($lowercasedClassName, [
						'self',
						'static',
						'parent',
					], true)) {
						if (!$this->isInClass()) {
							throw new \PHPStan\ShouldNotHappenException();
						}
						if ($lowercasedClassName === 'static') {
							return new StaticType($this->getClassReflection()->getName());
						}

						if ($lowercasedClassName === 'self') {
							return new ObjectType($this->getClassReflection()->getName());
						}

						if ($this->getClassReflection()->getParentClass() !== false) {
							return new ObjectType($this->getClassReflection()->getParentClass()->getName());
						}

						return new NonexistentParentClassType();
					}
				}

				return new ObjectType((string) $node->class);
			}
			if ($node->class instanceof Node\Stmt\Class_) {
				$anonymousClassReflection = $this->broker->getAnonymousClassReflection($node, $this);

				return new ObjectType($anonymousClassReflection->getName());
			}
		} elseif ($node instanceof Array_) {
			$arrayBuilder = ConstantArrayTypeBuilder::createEmpty();
			foreach ($node->items as $arrayItem) {
				$arrayBuilder->setOffsetValueType(
					$arrayItem->key !== null ? $this->getType($arrayItem->key) : null,
					$this->getType($arrayItem->value)
				);
			}
			return $arrayBuilder->getArray();

		} elseif ($node instanceof Int_) {
			return $this->getType($node->expr)->toInteger();
		} elseif ($node instanceof Bool_) {
			return $this->getType($node->expr)->toBoolean();
		} elseif ($node instanceof Double) {
			return $this->getType($node->expr)->toFloat();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
			return $this->getType($node->expr)->toString();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
			return $this->getType($node->expr)->toArray();
		} elseif ($node instanceof Node\Scalar\MagicConst\Line) {
			return new ConstantIntegerType($node->getLine());
		} elseif ($node instanceof Node\Scalar\MagicConst\Class_) {
			if (!$this->isInClass()) {
				return new ConstantStringType('');
			}

			return new ConstantStringType($this->getClassReflection()->getName());
		} elseif ($node instanceof Node\Scalar\MagicConst\Dir) {
			return new ConstantStringType(dirname($this->getFile()));
		} elseif ($node instanceof Node\Scalar\MagicConst\File) {
			return new ConstantStringType($this->getFile());
		} elseif ($node instanceof Node\Scalar\MagicConst\Namespace_) {
			if (!$this->isInClass()) {
				return new ConstantStringType('');
			}

			$className = $this->getClassReflection()->getName();
			$parts = explode('\\', $className);
			if (count($parts) <= 1) {
				return new ConstantStringType('');
			}

			return new ConstantStringType($parts[0]);
		} elseif ($node instanceof Node\Scalar\MagicConst\Method) {
			if ($this->isInAnonymousFunction()) {
				return new ConstantStringType('{closure}');
			}

			$function = $this->getFunction();
			if ($function === null) {
				return new ConstantStringType('');
			}
			if ($function instanceof MethodReflection) {
				return new ConstantStringType(
					sprintf('%s::%s', $function->getDeclaringClass()->getName(), $function->getName())
				);
			}

			return new ConstantStringType($function->getName());
		} elseif ($node instanceof Node\Scalar\MagicConst\Function_) {
			if ($this->isInAnonymousFunction()) {
				return new ConstantStringType('{closure}');
			}
			$function = $this->getFunction();
			if ($function === null) {
				return new ConstantStringType('');
			}

			return new ConstantStringType($function->getName());
		} elseif ($node instanceof Node\Scalar\MagicConst\Trait_) {
			if (!$this->isInTrait()) {
				return new ConstantStringType('');
			}
			return new ConstantStringType($this->getTraitReflection()->getName());
		} elseif ($node instanceof Object_) {
			$castToObject = static function (Type $type): Type {
				if ((new ObjectWithoutClassType())->isSuperTypeOf($type)->yes()) {
					return $type;
				}

				return new ObjectType('stdClass');
			};

			$exprType = $this->getType($node->expr);
			if ($exprType instanceof UnionType) {
				return TypeCombinator::union(...array_map($castToObject, $exprType->getTypes()));
			}

			return $castToObject($exprType);
		} elseif ($node instanceof Unset_) {
			return new NullType();
		} elseif ($node instanceof Expr\PostInc || $node instanceof Expr\PostDec) {
			return $this->getType($node->var);
		} elseif ($node instanceof Expr\PreInc || $node instanceof Expr\PreDec) {
			$varType = $this->getType($node->var);
			if ($varType instanceof ConstantScalarType) {
				$varValue = $varType->getValue();
				if ($node instanceof Expr\PreInc) {
					++$varValue;
				} else {
					--$varValue;
				}
				return $this->getTypeFromValue($varValue);
			}

			$stringType = new StringType();
			if ($stringType->isSuperTypeOf($varType)->yes()) {
				return $stringType;
			}

			return $varType->toNumber();
		}

		$exprString = $this->printer->prettyPrintExpr($node);
		if (isset($this->moreSpecificTypes[$exprString])) {
			return $this->moreSpecificTypes[$exprString]->getType();
		}

		if ($node instanceof ConstFetch) {
			$constName = strtolower((string) $node->name);
			if ($constName === 'true') {
				return new \PHPStan\Type\Constant\ConstantBooleanType(true);
			} elseif ($constName === 'false') {
				return new \PHPStan\Type\Constant\ConstantBooleanType(false);
			} elseif ($constName === 'null') {
				return new NullType();
			}

			if ($this->broker->hasConstant($node->name, $this)) {
				/** @var string $resolvedConstantName */
				$resolvedConstantName = $this->broker->resolveConstantName($node->name, $this);
				if ($resolvedConstantName === 'DIRECTORY_SEPARATOR') {
					return new UnionType([
						new ConstantStringType('/'),
						new ConstantStringType('\\'),
					]);
				}
				if ($resolvedConstantName === 'PATH_SEPARATOR') {
					return new UnionType([
						new ConstantStringType(':'),
						new ConstantStringType(';'),
					]);
				}
				if ($resolvedConstantName === 'PHP_EOL') {
					return new UnionType([
						new ConstantStringType("\n"),
						new ConstantStringType("\r\n"),
					]);
				}

				$constantType = $this->getTypeFromValue(constant($resolvedConstantName));
				if ($constantType instanceof ConstantType && in_array($resolvedConstantName, $this->dynamicConstantNames, true)) {
					return $constantType->generalize();
				}
				return $constantType;
			}

			return new ErrorType();
		} elseif ($node instanceof Node\Expr\ClassConstFetch && $node->name instanceof Node\Identifier) {
			$constantName = $node->name->name;
			if ($node->class instanceof Name) {
				$constantClass = (string) $node->class;
				$constantClassType = new ObjectType($constantClass);
				$namesToResolve = [
					'self',
					'parent',
				];
				if ($this->isInClass()) {
					if ($this->getClassReflection()->isFinal()) {
						$namesToResolve[] = 'static';
					} elseif (strtolower($constantClass) === 'static') {
						if (strtolower($constantName) === 'class') {
							return new StringType();
						}
						return new MixedType();
					}
				}
				if (in_array(strtolower($constantClass), $namesToResolve, true)) {
					$resolvedName = $this->resolveName($node->class);
					if ($resolvedName === 'parent' && strtolower($constantName) === 'class') {
						return new StringType();
					}
					$constantClassType = new ObjectType($resolvedName);
				}
			} else {
				$constantClassType = $this->getType($node->class);
			}

			if (strtolower($constantName) === 'class' && $constantClassType instanceof TypeWithClassName) {
				return new ConstantStringType($constantClassType->getClassName());
			}
			if ($constantClassType->hasConstant($constantName)) {
				$constant = $constantClassType->getConstant($constantName);
				$constantType = $this->getTypeFromValue($constant->getValue());
				$directClassNames = TypeUtils::getDirectClassNames($constantClassType);
				if (
					$constantType instanceof ConstantType &&
					count($directClassNames) === 1 &&
					in_array(sprintf('%s::%s', $this->broker->getClass($directClassNames[0])->getName(), $constantName), $this->dynamicConstantNames, true)
				) {
					return $constantType->generalize();
				}
				return $constantType;
			}

			return new ErrorType();
		}

		if ($node instanceof Expr\Ternary) {
			if ($node->if === null) {
				$conditionType = $this->filterByTruthyValue($node->cond, true)->getType($node->cond);
				$booleanConditionType = $conditionType->toBoolean();
				if ($booleanConditionType instanceof ConstantBooleanType) {
					if ($booleanConditionType->getValue()) {
						return $conditionType;
					}

					return $this->filterByFalseyValue($node->cond, true)->getType($node->else);
				}
				return TypeCombinator::union(
					$conditionType,
					$this->filterByFalseyValue($node->cond, true)->getType($node->else)
				);
			}

			$booleanConditionType = $this->getType($node->cond)->toBoolean();
			if ($booleanConditionType instanceof ConstantBooleanType) {
				if ($booleanConditionType->getValue()) {
					return $this->filterByTruthyValue($node->cond)->getType($node->if);
				}

				return $this->filterByFalseyValue($node->cond)->getType($node->else);
			}

			return TypeCombinator::union(
				$this->filterByTruthyValue($node->cond)->getType($node->if),
				$this->filterByFalseyValue($node->cond)->getType($node->else)
			);
		}

		if ($node instanceof Variable && is_string($node->name)) {
			if ($this->hasVariableType($node->name)->no()) {
				return new ErrorType();
			}

			return $this->getVariableType($node->name);
		}

		if ($node instanceof Expr\ArrayDimFetch && $node->dim !== null) {
			return $this->getTypeFromArrayDimFetch(
				$node,
				$this->getType($node->dim),
				$this->getType($node->var)
			);
		}

		if ($node instanceof MethodCall && $node->name instanceof Node\Identifier) {
			$methodCalledOnType = $this->getType($node->var);
			$referencedClasses = TypeUtils::getDirectClassNames($methodCalledOnType);
			$resolvedTypes = [];
			foreach ($referencedClasses as $referencedClass) {
				if (!$this->broker->hasClass($referencedClass)) {
					continue;
				}

				$methodClassReflection = $this->broker->getClass($referencedClass);
				if (!$methodClassReflection->hasMethod($node->name->name)) {
					if ($methodCalledOnType instanceof IntersectionType) {
						continue;
					}
					return new ErrorType();
				}

				$methodReflection = $methodClassReflection->getMethod($node->name->name, $this);
				foreach ($this->broker->getDynamicMethodReturnTypeExtensionsForClass($methodClassReflection->getName()) as $dynamicMethodReturnTypeExtension) {
					if (!$dynamicMethodReturnTypeExtension->isMethodSupported($methodReflection)) {
						continue;
					}

					$resolvedTypes[] = $dynamicMethodReturnTypeExtension->getTypeFromMethodCall($methodReflection, $node, $this);
				}
			}
			if (count($resolvedTypes) > 0) {
				return TypeCombinator::union(...$resolvedTypes);
			}

			if (!$methodCalledOnType->hasMethod($node->name->name)) {
				return new ErrorType();
			}
			$methodReflection = $methodCalledOnType->getMethod($node->name->name, $this);

			$methodReturnType = ParametersAcceptorSelector::selectFromArgs(
				$this,
				$node->args,
				$methodReflection->getVariants()
			)->getReturnType();
			if ($methodReturnType instanceof StaticResolvableType) {
				$calledOnThis = $this->getType($node->var) instanceof ThisType;
				if ($calledOnThis) {
					if ($this->isInClass()) {
						return $methodReturnType->changeBaseClass($this->getClassReflection()->getName());
					}
				} elseif (count($referencedClasses) === 1) {
					return $methodReturnType->resolveStatic($referencedClasses[0]);
				}
			}
			return $methodReturnType;
		}

		if ($node instanceof Expr\StaticCall && $node->name instanceof Node\Identifier) {
			if ($node->class instanceof Name) {
				$calleeType = new ObjectType($this->resolveName($node->class));
			} else {
				$calleeType = $this->getType($node->class);
			}

			if (!$calleeType->hasMethod($node->name->name)) {
				return new ErrorType();
			}
			$staticMethodReflection = $calleeType->getMethod($node->name->name, $this);
			$referencedClasses = TypeUtils::getDirectClassNames($calleeType);

			$resolvedTypes = [];
			foreach ($referencedClasses as $referencedClass) {
				if (!$this->broker->hasClass($referencedClass)) {
					continue;
				}

				$staticMethodClassReflection = $this->broker->getClass($referencedClass);
				foreach ($this->broker->getDynamicStaticMethodReturnTypeExtensionsForClass($staticMethodClassReflection->getName()) as $dynamicStaticMethodReturnTypeExtension) {
					if (!$dynamicStaticMethodReturnTypeExtension->isStaticMethodSupported($staticMethodReflection)) {
						continue;
					}

					$resolvedTypes[] = $dynamicStaticMethodReturnTypeExtension->getTypeFromStaticMethodCall($staticMethodReflection, $node, $this);
				}
			}
			if (count($resolvedTypes) > 0) {
				return TypeCombinator::union(...$resolvedTypes);
			}

			$staticMethodReturnType = ParametersAcceptorSelector::selectFromArgs(
				$this,
				$node->args,
				$staticMethodReflection->getVariants()
			)->getReturnType();

			if ($staticMethodReturnType instanceof StaticResolvableType) {
				if ($node->class instanceof Name) {
					$nodeClassString = strtolower((string) $node->class);
					if (in_array($nodeClassString, [
						'self',
						'static',
						'parent',
					], true) && $this->isInClass()) {
						return $staticMethodReturnType->changeBaseClass($this->getClassReflection()->getName());
					}
				}
				if (count($referencedClasses) === 1) {
					return $staticMethodReturnType->resolveStatic($referencedClasses[0]);
				}
			}
			return $staticMethodReturnType;
		}

		if ($node instanceof PropertyFetch && $node->name instanceof Node\Identifier) {
			$propertyFetchedOnType = $this->getType($node->var);
			if (!$propertyFetchedOnType->hasProperty($node->name->name)) {
				return new ErrorType();
			}

			return $propertyFetchedOnType->getProperty($node->name->name, $this)->getType();
		}

		if ($node instanceof Expr\StaticPropertyFetch && $node->name instanceof Node\VarLikeIdentifier && $node->class instanceof Name) {
			$staticPropertyHolderClass = $this->resolveName($node->class);
			if ($this->broker->hasClass($staticPropertyHolderClass)) {
				$staticPropertyClassReflection = $this->broker->getClass(
					$staticPropertyHolderClass
				);
				if (!$staticPropertyClassReflection->hasProperty($node->name->name)) {
					return new ErrorType();
				}

				return $staticPropertyClassReflection->getProperty($node->name->name, $this)->getType();
			}
		}

		if ($node instanceof FuncCall) {
			if ($node->name instanceof Expr) {
				$calledOnType = $this->getType($node->name);
				if ($calledOnType->isCallable()->no()) {
					return new ErrorType();
				}

				return ParametersAcceptorSelector::selectFromArgs(
					$this,
					$node->args,
					$calledOnType->getCallableParametersAcceptors($this)
				)->getReturnType();
			}

			if (!$this->broker->hasFunction($node->name, $this)) {
				return new ErrorType();
			}

			$functionReflection = $this->broker->getFunction($node->name, $this);
			foreach ($this->broker->getDynamicFunctionReturnTypeExtensions() as $dynamicFunctionReturnTypeExtension) {
				if (!$dynamicFunctionReturnTypeExtension->isFunctionSupported($functionReflection)) {
					continue;
				}

				return $dynamicFunctionReturnTypeExtension->getTypeFromFunctionCall($functionReflection, $node, $this);
			}

			return ParametersAcceptorSelector::selectFromArgs(
				$this,
				$node->args,
				$functionReflection->getVariants()
			)->getReturnType();
		}

		return new MixedType();
	}

	protected function getTypeFromArrayDimFetch(
		Expr\ArrayDimFetch $arrayDimFetch,
		Type $offsetType,
		Type $offsetAccessibleType
	): Type
	{
		if ($arrayDimFetch->dim === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		if ((new ObjectType(\ArrayAccess::class))->isSuperTypeOf($offsetAccessibleType)->yes()) {
			return $this->getType(
				new MethodCall(
					$arrayDimFetch->var,
					new Node\Identifier('offsetGet'),
					[
						new Node\Arg($arrayDimFetch->dim),
					]
				)
			);
		}

		return $offsetAccessibleType->getOffsetValueType($offsetType);
	}

	private function calculateFromScalars(Expr $node, ConstantScalarType $leftType, ConstantScalarType $rightType): Type
	{
		$leftValue = $leftType->getValue();
		$rightValue = $rightType->getValue();

		if ($node instanceof Node\Expr\BinaryOp\Spaceship) {
			return $this->getTypeFromValue($leftValue <=> $rightValue);
		}

		$leftNumberType = $leftType->toNumber();
		$rightNumberType = $rightType->toNumber();
		if (TypeCombinator::union($leftNumberType, $rightNumberType) instanceof ErrorType) {
			return new ErrorType();
		}

		if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		/** @var float|int $leftNumberValue */
		$leftNumberValue = $leftNumberType->getValue();

		/** @var float|int $rightNumberValue */
		$rightNumberValue = $rightNumberType->getValue();

		if ($node instanceof Node\Expr\BinaryOp\Plus || $node instanceof Node\Expr\AssignOp\Plus) {
			return $this->getTypeFromValue($leftNumberValue + $rightNumberValue);
		}

		if ($node instanceof Node\Expr\BinaryOp\Minus || $node instanceof Node\Expr\AssignOp\Minus) {
			return $this->getTypeFromValue($leftNumberValue - $rightNumberValue);
		}

		if ($node instanceof Node\Expr\BinaryOp\Mul || $node instanceof Node\Expr\AssignOp\Mul) {
			return $this->getTypeFromValue($leftNumberValue * $rightNumberValue);
		}

		if ($node instanceof Node\Expr\BinaryOp\Pow || $node instanceof Node\Expr\AssignOp\Pow) {
			return $this->getTypeFromValue($leftNumberValue ** $rightNumberValue);
		}

		if ($node instanceof Node\Expr\BinaryOp\Div || $node instanceof Node\Expr\AssignOp\Div) {
			return $this->getTypeFromValue($leftNumberValue / $rightNumberValue);
		}

		if ($node instanceof Node\Expr\BinaryOp\Mod || $node instanceof Node\Expr\AssignOp\Mod) {
			return $this->getTypeFromValue($leftNumberValue % $rightNumberValue);
		}

		if ($node instanceof Expr\BinaryOp\ShiftLeft || $node instanceof Expr\AssignOp\ShiftLeft) {
			return $this->getTypeFromValue($leftNumberValue << $rightNumberValue);
		}

		if ($node instanceof Expr\BinaryOp\ShiftRight || $node instanceof Expr\AssignOp\ShiftRight) {
			return $this->getTypeFromValue($leftNumberValue >> $rightNumberValue);
		}

		if ($node instanceof Expr\BinaryOp\BitwiseAnd || $node instanceof Expr\AssignOp\BitwiseAnd) {
			return $this->getTypeFromValue($leftNumberValue & $rightNumberValue);
		}

		if ($node instanceof Expr\BinaryOp\BitwiseOr || $node instanceof Expr\AssignOp\BitwiseOr) {
			return $this->getTypeFromValue($leftNumberValue | $rightNumberValue);
		}

		if ($node instanceof Expr\BinaryOp\BitwiseXor || $node instanceof Expr\AssignOp\BitwiseXor) {
			return $this->getTypeFromValue($leftNumberValue ^ $rightNumberValue);
		}

		return new MixedType();
	}

	public function resolveName(Name $name): string
	{
		$originalClass = (string) $name;
		if ($this->isInClass()) {
			if (in_array(strtolower($originalClass), [
				'self',
				'static',
			], true)) {
				return $this->getClassReflection()->getName();
			} elseif ($originalClass === 'parent') {
				$currentClassReflection = $this->getClassReflection();
				if ($currentClassReflection->getParentClass() !== false) {
					return $currentClassReflection->getParentClass()->getName();
				}
			}
		}

		return $originalClass;
	}

	/**
	 * @param mixed $value
	 */
	public function getTypeFromValue($value): Type
	{
		if (is_int($value)) {
			return new ConstantIntegerType($value);
		} elseif (is_float($value)) {
			return new ConstantFloatType($value);
		} elseif (is_bool($value)) {
			return new ConstantBooleanType($value);
		} elseif ($value === null) {
			return new NullType();
		} elseif (is_string($value)) {
			return new ConstantStringType($value);
		} elseif (is_array($value)) {
			$arrayBuilder = ConstantArrayTypeBuilder::createEmpty();
			foreach ($value as $k => $v) {
				$arrayBuilder->setOffsetValueType($this->getTypeFromValue($k), $this->getTypeFromValue($v));
			}
			return $arrayBuilder->getArray();
		}

		return new MixedType();
	}

	public function isSpecified(Expr $node): bool
	{
		$exprString = $this->printer->prettyPrintExpr($node);

		return isset($this->moreSpecificTypes[$exprString]);
	}

	public function enterClass(ClassReflection $classReflection): self
	{
		return $this->scopeFactory->create(
			$this->context->enterClass($classReflection),
			$this->isDeclareStrictTypes(),
			null,
			$this->getNamespace(),
			[
				'this' => VariableTypeHolder::createYes(new ThisType($classReflection->getName())),
			]
		);
	}

	public function enterTrait(ClassReflection $traitReflection): self
	{
		return $this->scopeFactory->create(
			$this->context->enterTrait($traitReflection),
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated()
		);
	}

	/**
	 * @param Node\Stmt\ClassMethod $classMethod
	 * @param Type[] $phpDocParameterTypes
	 * @param Type|null $phpDocReturnType
	 * @param Type|null $throwType
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 * @return self
	 */
	public function enterClassMethod(
		Node\Stmt\ClassMethod $classMethod,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $throwType,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	): self
	{
		if (!$this->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $this->enterFunctionLike(
			new PhpMethodFromParserNodeReflection(
				$this->getClassReflection(),
				$classMethod,
				$this->getRealParameterTypes($classMethod),
				$phpDocParameterTypes,
				$classMethod->returnType !== null,
				$this->getFunctionType($classMethod->returnType, $classMethod->returnType === null, false),
				$phpDocReturnType,
				$throwType,
				$isDeprecated,
				$isInternal,
				$isFinal
			)
		);
	}

	/**
	 * @param Node\FunctionLike $functionLike
	 * @return Type[]
	 */
	private function getRealParameterTypes(Node\FunctionLike $functionLike): array
	{
		$realParameterTypes = [];
		foreach ($functionLike->getParams() as $parameter) {
			if (!$parameter->var instanceof Variable || !is_string($parameter->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$realParameterTypes[$parameter->var->name] = $this->getFunctionType(
				$parameter->type,
				$this->isParameterValueNullable($parameter),
				$parameter->variadic
			);
		}

		return $realParameterTypes;
	}

	/**
	 * @param Node\Stmt\Function_ $function
	 * @param Type[] $phpDocParameterTypes
	 * @param Type|null $phpDocReturnType
	 * @param Type|null $throwType
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 * @return self
	 */
	public function enterFunction(
		Node\Stmt\Function_ $function,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $throwType,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	): self
	{
		return $this->enterFunctionLike(
			new PhpFunctionFromParserNodeReflection(
				$function,
				$this->getRealParameterTypes($function),
				$phpDocParameterTypes,
				$function->returnType !== null,
				$this->getFunctionType($function->returnType, $function->returnType === null, false),
				$phpDocReturnType,
				$throwType,
				$isDeprecated,
				$isInternal,
				$isFinal
			)
		);
	}

	private function enterFunctionLike(
		PhpFunctionFromParserNodeReflection $functionReflection
	): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach (ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getParameters() as $parameter) {
			$variableTypes[$parameter->getName()] = VariableTypeHolder::createYes($parameter->getType());
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$functionReflection,
			$this->getNamespace(),
			$variableTypes,
			[],
			null,
			null
		);
	}

	public function enterNamespace(string $namespaceName): self
	{
		return $this->scopeFactory->create(
			$this->context->beginFile(),
			$this->isDeclareStrictTypes(),
			null,
			$namespaceName
		);
	}

	public function enterClosureBind(?Type $thisType, string $scopeClass): self
	{
		$variableTypes = $this->getVariableTypes();

		if ($thisType !== null) {
			$variableTypes['this'] = VariableTypeHolder::createYes($thisType);
		} else {
			unset($variableTypes['this']);
		}

		if ($scopeClass === 'static' && $this->isInClass()) {
			$scopeClass = $this->getClassReflection()->getName();
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->moreSpecificTypes,
			$scopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated()
		);
	}

	public function enterClosureCall(Type $thisType): self
	{
		$variableTypes = $this->getVariableTypes();
		$variableTypes['this'] = VariableTypeHolder::createYes($thisType);

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->moreSpecificTypes,
			$thisType instanceof TypeWithClassName ? $thisType->getClassName() : null,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated()
		);
	}

	public function isInClosureBind(): bool
	{
		return $this->inClosureBindScopeClass !== null;
	}

	public function enterAnonymousClass(ClassReflection $anonymousClass): self
	{
		return $this->enterClass($anonymousClass);
	}

	public function enterAnonymousFunction(
		Expr\Closure $closure
	): self
	{
		$variableTypes = [];
		foreach ($closure->params as $parameter) {
			$isNullable = $this->isParameterValueNullable($parameter);

			if (!$parameter->var instanceof Variable || !is_string($parameter->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$variableTypes[$parameter->var->name] = VariableTypeHolder::createYes(
				$this->getFunctionType($parameter->type, $isNullable, $parameter->variadic)
			);
		}

		foreach ($closure->uses as $use) {
			if (!is_string($use->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if ($this->hasVariableType($use->var->name)->no()) {
				if ($use->byRef) {
					if ($this->isInExpressionAssign(new Variable($use->var->name))) {
						$variableTypes[$use->var->name] = VariableTypeHolder::createYes(
							$this->getType($closure)
						);
						continue;
					}
					$variableTypes[$use->var->name] = VariableTypeHolder::createYes(new NullType());
				}
				continue;
			}
			$variableTypes[$use->var->name] = VariableTypeHolder::createYes($this->getVariableType($use->var->name));
		}

		if ($this->hasVariableType('this')->yes()) {
			$variableTypes['this'] = VariableTypeHolder::createYes($this->getVariableType('this'));
		}

		$returnType = $this->getFunctionType($closure->returnType, $closure->returnType === null, false);

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			[],
			$this->inClosureBindScopeClass,
			$returnType,
			$this->getInFunctionCall()
		);
	}

	public function isParameterValueNullable(Node\Param $parameter): bool
	{
		if ($parameter->default instanceof ConstFetch) {
			return strtolower((string) $parameter->default->name) === 'null';
		}

		return false;
	}

	/**
	 * @param \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\NullableType|null $type
	 * @param bool $isNullable
	 * @param bool $isVariadic
	 * @return Type
	 */
	public function getFunctionType($type, bool $isNullable, bool $isVariadic): Type
	{
		if ($isNullable) {
			return TypeCombinator::addNull(
				$this->getFunctionType($type, false, $isVariadic)
			);
		}
		if ($isVariadic) {
			return new ArrayType(new IntegerType(), $this->getFunctionType(
				$type,
				false,
				false
			));
		}
		if ($type === null) {
			return new MixedType();
		} elseif ($type instanceof Name) {
			$className = (string) $type;
			$lowercasedClassName = strtolower($className);
			if ($this->isInClass() && in_array($lowercasedClassName, ['self', 'static'], true)) {
				$className = $this->getClassReflection()->getName();
			} elseif (
				$lowercasedClassName === 'parent'
			) {
				if ($this->isInClass() && $this->getClassReflection()->getParentClass() !== false) {
					return new ObjectType($this->getClassReflection()->getParentClass()->getName());
				}

				return new NonexistentParentClassType();
			}
			return new ObjectType($className);
		} elseif ($type instanceof Node\NullableType) {
			return $this->getFunctionType($type->type, true, $isVariadic);
		}

		$type = $type->name;
		if ($type === 'string') {
			return new StringType();
		} elseif ($type === 'int') {
			return new IntegerType();
		} elseif ($type === 'bool') {
			return new BooleanType();
		} elseif ($type === 'float') {
			return new FloatType();
		} elseif ($type === 'callable') {
			return new CallableType();
		} elseif ($type === 'array') {
			return new ArrayType(new MixedType(), new MixedType());
		} elseif ($type === 'iterable') {
			return new IterableType(new MixedType(), new MixedType());
		} elseif ($type === 'void') {
			return new VoidType();
		} elseif ($type === 'object') {
			return new ObjectWithoutClassType();
		}

		return new MixedType();
	}

	public function enterForeach(Expr $iteratee, string $valueName, ?string $keyName): self
	{
		$iterateeType = $this->getType($iteratee);
		$scope = $this->assignVariable($valueName, $iterateeType->getIterableValueType(), TrinaryLogic::createYes());

		if ($keyName !== null) {
			$scope = $scope->assignVariable($keyName, $iterateeType->getIterableKeyType(), TrinaryLogic::createYes());
		}

		return $scope;
	}

	/**
	 * @param \PhpParser\Node\Name[] $classes
	 * @param string $variableName
	 * @return Scope
	 */
	public function enterCatch(array $classes, string $variableName): self
	{
		$type = TypeCombinator::union(...array_map(static function (string $class): ObjectType {
			return new ObjectType($class);
		}, $classes));

		return $this->assignVariable(
			$variableName,
			TypeCombinator::intersect($type, new ObjectType(\Throwable::class)),
			TrinaryLogic::createYes()
		);
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $functionCall
	 * @return self
	 */
	public function enterFunctionCall($functionCall): self
	{
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$functionCall,
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function enterExpressionAssign(Expr $expr): self
	{
		$currentlyAssignedExpressions = $this->currentlyAssignedExpressions;
		$currentlyAssignedExpressions[] = $this->printer->prettyPrintExpr($expr);

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->isInFirstLevelStatement(),
			$currentlyAssignedExpressions
		);
	}

	public function isInExpressionAssign(Expr $expr): bool
	{
		$exprString = $this->printer->prettyPrintExpr($expr);
		return in_array($exprString, $this->currentlyAssignedExpressions, true);
	}

	public function assignVariable(
		string $variableName,
		Type $type,
		TrinaryLogic $certainty
	): self
	{
		if ($certainty->no()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$existingCertainty = $this->hasVariableType($variableName);
		if (!$existingCertainty->no()) {
			$certainty = $certainty->or($existingCertainty);
		}

		$variableTypes = $this->getVariableTypes();
		$variableTypes[$variableName] = new VariableTypeHolder($type, $certainty);

		$variableString = $this->printer->prettyPrintExpr(new Variable($variableName));
		$moreSpecificTypeHolders = $this->moreSpecificTypes;
		foreach (array_keys($moreSpecificTypeHolders) as $key) {
			$matches = \Nette\Utils\Strings::match((string) $key, '#(\$[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*)#');
			if ($matches === null) {
				continue;
			}

			if ($matches[1] !== $variableString) {
				continue;
			}

			unset($moreSpecificTypeHolders[$key]);
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$moreSpecificTypeHolders,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement,
			$this->currentlyAssignedExpressions
		);
	}

	public function unsetExpression(Expr $expr): self
	{
		if ($expr instanceof Variable && is_string($expr->name)) {
			if ($this->hasVariableType($expr->name)->no()) {
				return $this;
			}
			$variableTypes = $this->getVariableTypes();
			unset($variableTypes[$expr->name]);

			return $this->scopeFactory->create(
				$this->context,
				$this->isDeclareStrictTypes(),
				$this->getFunction(),
				$this->getNamespace(),
				$variableTypes,
				$this->moreSpecificTypes,
				$this->inClosureBindScopeClass,
				$this->getAnonymousFunctionReturnType(),
				$this->getInFunctionCall(),
				$this->isNegated(),
				$this->inFirstLevelStatement
			);
		} elseif ($expr instanceof Expr\ArrayDimFetch && $expr->dim !== null) {
			$constantArrays = TypeUtils::getConstantArrays($this->getType($expr->var));
			if (count($constantArrays) > 0) {
				$unsetArrays = [];
				$dimType = $this->getType($expr->dim);
				foreach ($constantArrays as $constantArray) {
					$unsetArrays[] = $constantArray->unsetOffset($dimType);
				}
				return $this->specifyExpressionType(
					$expr->var,
					TypeCombinator::union(...$unsetArrays)
				);
			}
		}

		return $this;
	}

	public function intersectVariables(Scope $otherScope): self
	{
		$ourVariableTypeHolders = $this->getVariableTypes();
		$theirVariableTypeHolders = $otherScope->getVariableTypes();
		$intersectedVariableTypeHolders = [];
		foreach ($theirVariableTypeHolders as $name => $variableTypeHolder) {
			if (isset($ourVariableTypeHolders[$name])) {
				$intersectedVariableTypeHolders[$name] = $ourVariableTypeHolders[$name]->and($variableTypeHolder);
			} else {
				$intersectedVariableTypeHolders[$name] = VariableTypeHolder::createMaybe($variableTypeHolder->getType());
			}
		}

		foreach ($ourVariableTypeHolders as $name => $variableTypeHolder) {
			$variableNode = new Variable($name);
			if ($otherScope->isSpecified($variableNode)) {
				$intersectedVariableTypeHolders[$name] = VariableTypeHolder::createYes(
					TypeCombinator::union(
						$otherScope->getType($variableNode),
						$variableTypeHolder->getType()
					)
				);
				continue;
			}
			if (isset($theirVariableTypeHolders[$name])) {
				continue;
			}

			$intersectedVariableTypeHolders[$name] = VariableTypeHolder::createMaybe($variableTypeHolder->getType());
		}

		$ourSpecifiedTypeHolders = $this->moreSpecificTypes;
		$theirSpecifiedTypeHolders = $otherScope->moreSpecificTypes;
		$intersectedSpecifiedTypes = [];

		foreach ($theirSpecifiedTypeHolders as $exprString => $theirSpecifiedTypeHolder) {
			$matches = \Nette\Utils\Strings::match((string) $exprString, '#^\$([a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*)$#');
			if ($matches !== null) {
				continue;
			}
			if (isset($ourSpecifiedTypeHolders[$exprString])) {
				$intersectedSpecifiedTypes[$exprString] = $ourSpecifiedTypeHolders[$exprString]->and($theirSpecifiedTypeHolder);
			} else {
				$intersectedSpecifiedTypes[$exprString] = VariableTypeHolder::createMaybe($theirSpecifiedTypeHolder->getType());
			}
		}

		foreach ($this->moreSpecificTypes as $exprString => $specificTypeHolder) {
			$matches = \Nette\Utils\Strings::match((string) $exprString, '#^\$([a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*)$#');
			if ($matches !== null) {
				continue;
			}
			if (isset($otherScope->moreSpecificTypes[$exprString])) {
				$intersectedSpecifiedTypes[$exprString] = VariableTypeHolder::createYes(
					TypeCombinator::union(
						$otherScope->moreSpecificTypes[$exprString]->getType(),
						$specificTypeHolder->getType()
					)
				);
				continue;
			}
			if (isset($theirVariableTypeHolders[$exprString])) {
				continue;
			}

			$intersectedSpecifiedTypes[$exprString] = VariableTypeHolder::createMaybe($specificTypeHolder->getType());
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$intersectedVariableTypeHolders,
			$intersectedSpecifiedTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function createIntersectedScope(self $otherScope): self
	{
		$variableTypes = [];
		foreach ($otherScope->getVariableTypes() as $name => $variableTypeHolder) {
			$variableTypes[$name] = $variableTypeHolder;
		}

		$specifiedTypes = [];
		foreach ($otherScope->moreSpecificTypes as $exprString => $specificTypeHolder) {
			$matches = \Nette\Utils\Strings::match((string) $exprString, '#^\$([a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*)$#');
			if ($matches !== null) {
				$variableTypes[$matches[1]] = $specificTypeHolder;
				continue;
			}
			$specifiedTypes[$exprString] = $specificTypeHolder;
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$specifiedTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function mergeWithIntersectedScope(self $intersectedScope): self
	{
		$variableTypeHolders = $this->variableTypes;
		$specifiedTypeHolders = $this->moreSpecificTypes;
		foreach ($intersectedScope->getVariableTypes() as $name => $theirVariableTypeHolder) {
			if (isset($variableTypeHolders[$name])) {
				$type = $theirVariableTypeHolder->getType();
				if ($theirVariableTypeHolder->getCertainty()->maybe()) {
					$type = TypeCombinator::union($type, $variableTypeHolders[$name]->getType());
				}
				$theirVariableTypeHolder = new VariableTypeHolder(
					$type,
					$theirVariableTypeHolder->getCertainty()->or($variableTypeHolders[$name]->getCertainty())
				);
			}

			$variableTypeHolders[$name] = $theirVariableTypeHolder;

			$exprString = $this->printer->prettyPrintExpr(new Variable($name));
			unset($specifiedTypeHolders[$exprString]);
		}

		foreach ($intersectedScope->moreSpecificTypes as $exprString => $theirTypeHolder) {
			if (isset($specifiedTypeHolders[$exprString])) {
				$type = $theirTypeHolder->getType();
				if ($theirTypeHolder->getCertainty()->maybe()) {
					$type = TypeCombinator::union($type, $specifiedTypeHolders[$exprString]->getType());
				}
				$theirTypeHolder = new VariableTypeHolder(
					$type,
					$theirTypeHolder->getCertainty()->or($specifiedTypeHolders[$exprString]->getCertainty())
				);
			}

			if (!$theirTypeHolder->getCertainty()->yes()) {
				continue;
			}

			$specifiedTypeHolders[$exprString] = $theirTypeHolder;
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypeHolders,
			$specifiedTypeHolders,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function removeSpecified(self $initialScope): self
	{
		$variableTypeHolders = $this->variableTypes;
		foreach ($variableTypeHolders as $name => $holder) {
			if (!$holder->getCertainty()->yes()) {
				continue;
			}
			$node = new Variable($name);
			if ($this->isSpecified($node) && !$initialScope->hasVariableType($name)->no()) {
				$variableTypeHolders[$name] = VariableTypeHolder::createYes(TypeCombinator::remove($initialScope->getVariableType($name), $this->getType($node)));
				continue;
			}
		}

		$moreSpecificTypeHolders = $this->moreSpecificTypes;
		foreach (array_keys($moreSpecificTypeHolders) as $exprString) {
			if (isset($initialScope->moreSpecificTypes[$exprString])) {
				continue;
			}

			unset($moreSpecificTypeHolders[$exprString]);
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypeHolders,
			$moreSpecificTypeHolders,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function removeVariables(self $otherScope, bool $all): self
	{
		$ourVariableTypeHolders = $this->getVariableTypes();
		foreach ($otherScope->getVariableTypes() as $name => $theirVariableTypeHolder) {
			if ($all) {
				if (
					isset($ourVariableTypeHolders[$name])
					&& $ourVariableTypeHolders[$name]->getCertainty()->equals($theirVariableTypeHolder->getCertainty())
				) {
					unset($ourVariableTypeHolders[$name]);
				}
			} else {
				if (
					isset($ourVariableTypeHolders[$name])
					&& $theirVariableTypeHolder->getType()->equals($ourVariableTypeHolders[$name]->getType())
					&& $ourVariableTypeHolders[$name]->getCertainty()->equals($theirVariableTypeHolder->getCertainty())
				) {
					unset($ourVariableTypeHolders[$name]);
				}
			}
		}

		$ourTypeHolders = $this->moreSpecificTypes;
		foreach ($otherScope->moreSpecificTypes as $exprString => $theirTypeHolder) {
			if ($all) {
				if (
					isset($ourTypeHolders[$exprString])
					&& $ourTypeHolders[$exprString]->getCertainty()->equals($theirTypeHolder->getCertainty())
				) {
					unset($ourVariableTypeHolders[$exprString]);
				}
			} else {
				if (
					isset($ourTypeHolders[$exprString])
					&& $theirTypeHolder->getType()->equals($ourTypeHolders[$exprString]->getType())
					&& $ourTypeHolders[$exprString]->getCertainty()->equals($theirTypeHolder->getCertainty())
				) {
					unset($ourVariableTypeHolders[$exprString]);
				}
			}
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$ourVariableTypeHolders,
			$ourTypeHolders,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function specifyExpressionType(Expr $expr, Type $type): self
	{
		if ($expr instanceof Node\Scalar || $expr instanceof Array_) {
			return $this;
		}

		$exprString = $this->printer->prettyPrintExpr($expr);

		$scope = $this;

		if ($expr instanceof Variable && is_string($expr->name)) {
			$variableName = $expr->name;

			$variableTypes = $this->getVariableTypes();
			$variableTypes[$variableName] = VariableTypeHolder::createYes($type);

			$moreSpecificTypes = $this->moreSpecificTypes;
			$moreSpecificTypes[$exprString] = $variableTypes[$variableName];

			return $this->scopeFactory->create(
				$this->context,
				$this->isDeclareStrictTypes(),
				$this->getFunction(),
				$this->getNamespace(),
				$variableTypes,
				$moreSpecificTypes,
				$this->inClosureBindScopeClass,
				$this->getAnonymousFunctionReturnType(),
				$this->getInFunctionCall(),
				$this->isNegated(),
				$this->inFirstLevelStatement
			);
		} elseif ($expr instanceof Expr\ArrayDimFetch && $expr->dim !== null) {
			$constantArrays = TypeUtils::getConstantArrays($this->getType($expr->var));
			if (count($constantArrays) > 0) {
				$setArrays = [];
				$dimType = $this->getType($expr->dim);
				foreach ($constantArrays as $constantArray) {
					$setArrays[] = $constantArray->setOffsetValueType($dimType, $type);
				}
				$scope = $this->specifyExpressionType(
					$expr->var,
					TypeCombinator::union(...$setArrays)
				);
			}
		}

		return $scope->addMoreSpecificTypes([
			$exprString => $type,
		]);
	}

	public function unspecifyExpressionType(Expr $expr): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);
		$moreSpecificTypeHolders = $this->moreSpecificTypes;
		if (isset($moreSpecificTypeHolders[$exprString]) && !$moreSpecificTypeHolders[$exprString]->getType() instanceof MixedType) {
			unset($moreSpecificTypeHolders[$exprString]);
			return $this->scopeFactory->create(
				$this->context,
				$this->isDeclareStrictTypes(),
				$this->getFunction(),
				$this->getNamespace(),
				$this->getVariableTypes(),
				$moreSpecificTypeHolders,
				$this->inClosureBindScopeClass,
				$this->getAnonymousFunctionReturnType(),
				$this->getInFunctionCall(),
				$this->isNegated(),
				$this->inFirstLevelStatement
			);
		}

		return $this;
	}

	public function removeTypeFromExpression(Expr $expr, Type $type): self
	{
		return $this->specifyExpressionType(
			$expr,
			TypeCombinator::remove($this->getType($expr), $type)
		);
	}

	public function filterByTruthyValue(Expr $expr, bool $defaultHandleFunctions = false): self
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, TypeSpecifierContext::createTruthy(), $defaultHandleFunctions);
		return $this->filterBySpecifiedTypes($specifiedTypes);
	}

	public function filterByFalseyValue(Expr $expr, bool $defaultHandleFunctions = false): self
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, TypeSpecifierContext::createFalsey(), $defaultHandleFunctions);
		return $this->filterBySpecifiedTypes($specifiedTypes);
	}

	public function filterBySpecifiedTypes(SpecifiedTypes $specifiedTypes): self
	{
		$typeSpecifications = [];
		foreach ($specifiedTypes->getSureTypes() as $exprString => [$expr, $type]) {
			$typeSpecifications[] = [
				'sure' => true,
				'exprString' => $exprString,
				'expr' => $expr,
				'type' => $type,
			];
		}
		foreach ($specifiedTypes->getSureNotTypes() as $exprString => [$expr, $type]) {
			$typeSpecifications[] = [
				'sure' => false,
				'exprString' => $exprString,
				'expr' => $expr,
				'type' => $type,
			];
		}

		usort($typeSpecifications, static function (array $a, array $b): int {
			$length = strlen((string) $a['exprString']) - strlen((string) $b['exprString']);
			if ($length !== 0) {
				return $length;
			}

			return $b['sure'] - $a['sure'];
		});

		$scope = $this;
		foreach ($typeSpecifications as $typeSpecification) {
			$expr = $typeSpecification['expr'];
			$type = $typeSpecification['type'];
			if ($typeSpecification['sure']) {
				$type = TypeCombinator::intersect($type, $this->getType($expr));
				$scope = $scope->specifyExpressionType($expr, $type);
			} else {
				$scope = $scope->removeTypeFromExpression($expr, $type);
			}
		}

		return $scope;
	}

	public function specifyFetchedStaticPropertyFromIsset(Expr\StaticPropertyFetch $expr): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);

		return $this->addMoreSpecificTypes([
			$exprString => new MixedType(),
		]);
	}

	public function enterNegation(): self
	{
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			!$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function enterFirstLevelStatements(): self
	{
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			true,
			$this->currentlyAssignedExpressions
		);
	}

	public function exitFirstLevelStatements(): self
	{
		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			false,
			$this->currentlyAssignedExpressions
		);
	}

	public function isInFirstLevelStatement(): bool
	{
		return $this->inFirstLevelStatement;
	}

	public function isNegated(): bool
	{
		return $this->negated;
	}

	/**
	 * @param Type[] $types
	 * @return self
	 */
	private function addMoreSpecificTypes(array $types): self
	{
		$moreSpecificTypeHolders = $this->moreSpecificTypes;
		foreach ($types as $exprString => $type) {
			$moreSpecificTypeHolders[$exprString] = VariableTypeHolder::createYes($type);
		}

		return $this->scopeFactory->create(
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$moreSpecificTypeHolders,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function canAccessProperty(PropertyReflection $propertyReflection): bool
	{
		return $this->canAccessClassMember($propertyReflection);
	}

	public function canCallMethod(MethodReflection $methodReflection): bool
	{
		if ($this->canAccessClassMember($methodReflection)) {
			return true;
		}

		return $this->canAccessClassMember($methodReflection->getPrototype());
	}

	public function canAccessConstant(ConstantReflection $constantReflection): bool
	{
		return $this->canAccessClassMember($constantReflection);
	}

	private function canAccessClassMember(ClassMemberReflection $classMemberReflection): bool
	{
		if ($classMemberReflection->isPublic()) {
			return true;
		}

		if ($this->inClosureBindScopeClass !== null && $this->broker->hasClass($this->inClosureBindScopeClass)) {
			$currentClassReflection = $this->broker->getClass($this->inClosureBindScopeClass);
		} elseif ($this->isInClass()) {
			$currentClassReflection = $this->getClassReflection();
		} else {
			return false;
		}

		$classReflectionName = $classMemberReflection->getDeclaringClass()->getName();
		if ($classMemberReflection->isPrivate()) {
			return $currentClassReflection->getName() === $classReflectionName;
		}

		// protected

		if (
			$currentClassReflection->getName() === $classReflectionName
			|| $currentClassReflection->isSubclassOf($classReflectionName)
		) {
			return true;
		}

		return $classMemberReflection->getDeclaringClass()->isSubclassOf($currentClassReflection->getName());
	}

	/**
	 * @return string[]
	 */
	public function debug(): array
	{
		$descriptions = [];
		foreach ($this->getVariableTypes() as $name => $variableTypeHolder) {
			$key = sprintf('$%s (%s)', $name, $variableTypeHolder->getCertainty()->describe());
			$descriptions[$key] = $variableTypeHolder->getType()->describe(VerbosityLevel::value());
		}
		foreach ($this->moreSpecificTypes as $exprString => $typeHolder) {
			$key = sprintf(
				'%s-specified (%s)',
				$exprString,
				$typeHolder->getCertainty()->describe()
			);
			$descriptions[$key] = $typeHolder->getType()->describe(VerbosityLevel::value());
		}

		return $descriptions;
	}

}
