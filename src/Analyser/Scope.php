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
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
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
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;

class Scope
{

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

	/** @var \PHPStan\Type\Type[] */
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

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Analyser\TypeSpecifier $typeSpecifier
	 * @param \PHPStan\Analyser\ScopeContext $context
	 * @param bool $declareStrictTypes
	 * @param \PHPStan\Reflection\FunctionReflection|MethodReflection|null $function
	 * @param string|null $namespace
	 * @param \PHPStan\Analyser\VariableTypeHolder[] $variablesTypes
	 * @param \PHPStan\Type\Type[] $moreSpecificTypes
	 * @param string|null $inClosureBindScopeClass
	 * @param \PHPStan\Type\Type|null $inAnonymousFunctionReturnType
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null $inFunctionCall
	 * @param bool $negated
	 * @param bool $inFirstLevelStatement
	 * @param string[] $currentlyAssignedExpressions
	 */
	public function __construct(
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
		array $currentlyAssignedExpressions = []
	)
	{
		if ($namespace === '') {
			$namespace = null;
		}

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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
	 * @return null|\PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection
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
	 * @return \PHPStan\Analyser\VariableTypeHolder[]
	 */
	private function getVariableTypes(): array
	{
		return $this->variableTypes;
	}

	public function hasVariableType(string $variableName): TrinaryLogic
	{
		if (!isset($this->variableTypes[$variableName])) {
			return TrinaryLogic::createNo();
		}

		return $this->variableTypes[$variableName]->getCertainty();
	}

	public function getVariableType(string $variableName): Type
	{
		if ($this->hasVariableType($variableName)->no()) {
			throw new \PHPStan\Analyser\UndefinedVariableException($this, $variableName);
		}

		return $this->variableTypes[$variableName]->getType();
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
			|| $node instanceof Expr\Isset_
			|| $node instanceof Expr\Empty_
		) {
			return new BooleanType();
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
			if ($expressionType instanceof NeverType) {
				return new ConstantBooleanType(false);
			}
			$isExpressionObject = (new ObjectWithoutClassType())->isSuperTypeOf($expressionType);
			if (!$isExpressionObject->no() && $type instanceof StringType) {
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
			if ($type instanceof ConstantIntegerType) {
				return new ConstantIntegerType(-$type->getValue());
			}

			if ($type instanceof ConstantFloatType) {
				return new ConstantFloatType(-$type->getValue());
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

			$rightType = $this->getType($right)->toNumber();
			if (
				$rightType instanceof ConstantScalarType
				&& (
					$rightType->getValue() === 0
					|| $rightType->getValue() === 0.0
				)
			) {
				return new ErrorType();
			}
		}

		if ($node instanceof Node\Expr\BinaryOp || $node instanceof Node\Expr\AssignOp) {
			if ($node instanceof Node\Expr\AssignOp) {
				$left = $node->var;
				$right = $node->expr;
			} else {
				$left = $node->left;
				$right = $node->right;
			}

			$leftType = $this->getType($left);
			$rightType = $this->getType($right);

			if ($leftType instanceof ConstantScalarType && $rightType instanceof ConstantScalarType) {
				$leftValue = $leftType->getValue();
				$rightValue = $rightType->getValue();

				if ($node instanceof Node\Expr\BinaryOp\Coalesce) {
					return $this->getTypeFromValue($leftValue ?? $rightValue);
				}

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

				if (($node instanceof Node\Expr\BinaryOp\Div || $node instanceof Node\Expr\AssignOp\Div)) {
					return $this->getTypeFromValue($leftNumberValue / $rightNumberValue);
				}

				if (($node instanceof Node\Expr\BinaryOp\Mod || $node instanceof Node\Expr\AssignOp\Mod)) {
					return $this->getTypeFromValue($leftNumberValue % $rightNumberValue);
				}

				if ($node instanceof Expr\BinaryOp\ShiftLeft || $node instanceof Expr\BinaryOp\ShiftLeft) {
					return $this->getTypeFromValue($leftNumberValue << $rightNumberValue);
				}

				if ($node instanceof Expr\BinaryOp\ShiftRight || $node instanceof Expr\BinaryOp\ShiftRight) {
					return $this->getTypeFromValue($leftNumberValue >> $rightNumberValue);
				}

				if ($node instanceof Expr\BinaryOp\BitwiseAnd || $node instanceof Expr\BinaryOp\BitwiseAnd) {
					return $this->getTypeFromValue($leftNumberValue & $rightNumberValue);
				}

				if ($node instanceof Expr\BinaryOp\BitwiseOr || $node instanceof Expr\BinaryOp\BitwiseOr) {
					return $this->getTypeFromValue($leftNumberValue | $rightNumberValue);
				}

				if ($node instanceof Expr\BinaryOp\BitwiseXor || $node instanceof Expr\BinaryOp\BitwiseXor) {
					return $this->getTypeFromValue($leftNumberValue ^ $rightNumberValue);
				}
			}
		}

		if ($node instanceof Node\Expr\BinaryOp\Mod || $node instanceof Expr\AssignOp\Mod) {
			return new IntegerType();
		}

		if ($node instanceof Expr\BinaryOp\Spaceship) {
			return new IntegerType();
		}

		if ($node instanceof Expr\BinaryOp\Coalesce) {
			return TypeCombinator::union(
				TypeCombinator::removeNull($this->getType($node->left)),
				$this->getType($node->right)
			);
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

			if (
				($node instanceof Expr\AssignOp\Plus || $node instanceof Expr\BinaryOp\Plus)
				&& $leftType instanceof ArrayType
				&& $rightType instanceof ArrayType
			) {
				if ($leftType instanceof ConstantArrayType && $rightType instanceof ConstantArrayType) {
					$newArrayType = $rightType;
					foreach ($leftType->getKeyTypes() as $keyType) {
						$newArrayType = $newArrayType->setOffsetValueType(
							$keyType,
							$leftType->getOffsetValueType($keyType)
						);
					}

					return $newArrayType;
				}

				return new ArrayType(
					TypeCombinator::union($leftType->getKeyType(), $rightType->getKeyType()),
					TypeCombinator::union($leftType->getItemType(), $rightType->getItemType()),
					$leftType->isItemTypeInferredFromLiteralArray() || $rightType->isItemTypeInferredFromLiteralArray()
				);
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

			if ($node instanceof Expr\AssignOp\Div || $node instanceof Expr\BinaryOp\Div) {
				return new UnionType([new IntegerType(), new FloatType()]);
			}

			return TypeCombinator::union($leftNumberType, $rightNumberType);
		}

		if ($node instanceof LNumber) {
			return new ConstantIntegerType($node->value);
		} elseif ($node instanceof ConstFetch) {
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
				if ($resolvedConstantName === 'ICONV_IMPL') {
					return new StringType();
				}

				return $this->getTypeFromValue(constant($resolvedConstantName));
			}
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
			$optional = false;
			foreach ($node->params as $param) {
				if ($param->default !== null) {
					$optional = true;
				}
				if ($param->variadic) {
					$isVariadic = true;
				}
				$parameters[] = new NativeParameterReflection(
					$param->name,
					$optional,
					$this->getFunctionType($param->type, $param->type === null, $param->variadic),
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

						if ($lowercasedClassName === 'parent') {
							if ($this->getClassReflection()->getParentClass() !== false) {
								return new ObjectType($this->getClassReflection()->getParentClass()->getName());
							}

							return new NonexistentParentClassType();
						}
					}
				}

				return new ObjectType((string) $node->class);
			}
		} elseif ($node instanceof Array_) {
			$array = new ConstantArrayType([], []);
			foreach ($node->items as $arrayItem) {
				$array = $array->setOffsetValueType(
					$arrayItem->key !== null ? $this->getType($arrayItem->key) : null,
					$this->getType($arrayItem->value)
				);
			}
			return $array;

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
				throw new \PHPStan\ShouldNotHappenException();
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
		} elseif ($node instanceof Node\Scalar\MagicConst\Class_) {
			if (!$this->isInClass()) {
				return new ConstantStringType('');
			}

			return new ConstantStringType($this->getClassReflection()->getName());
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
			$castToObject = function (Type $type): Type {
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
		} elseif ($node instanceof Node\Expr\ClassConstFetch && is_string($node->name)) {
			if ($node->class instanceof Name) {
				$constantClass = (string) $node->class;
				$constantClassType = new ObjectType($constantClass);
				if (in_array(strtolower($constantClass), [
					'self',
					'static',
					'parent',
				], true)) {
					$resolvedName = $this->resolveName($node->class);
					$constantClassType = new ObjectType($resolvedName);
				}
			} else {
				$constantClassType = $this->getType($node->class);
			}

			$constantName = $node->name;
			if (strtolower($constantName) === 'class' && $constantClassType instanceof TypeWithClassName) {
				return new ConstantStringType($constantClassType->getClassName());
			}
			if ($constantClassType->hasConstant($constantName)) {
				$constant = $constantClassType->getConstant($constantName);
				return $this->getTypeFromValue($constant->getValue());
			}
		}

		$exprString = $this->printer->prettyPrintExpr($node);
		if (isset($this->moreSpecificTypes[$exprString])) {
			return $this->moreSpecificTypes[$exprString];
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
			$offsetType = $this->getType($node->dim);
			$offsetAccessibleType = $this->getType($node->var);
			return $offsetAccessibleType->getOffsetValueType($offsetType);
		}

		if ($node instanceof MethodCall && is_string($node->name)) {
			$methodCalledOnType = $this->getType($node->var);
			$referencedClasses = $methodCalledOnType->getReferencedClasses();
			if (
				count($referencedClasses) === 1
				&& $this->broker->hasClass($referencedClasses[0])
			) {
				$methodClassReflection = $this->broker->getClass($referencedClasses[0]);
				if (!$methodClassReflection->hasMethod($node->name)) {
					return new ErrorType();
				}

				$methodReflection = $methodClassReflection->getMethod($node->name, $this);
				foreach ($this->broker->getDynamicMethodReturnTypeExtensionsForClass($methodClassReflection->getName()) as $dynamicMethodReturnTypeExtension) {
					if (!$dynamicMethodReturnTypeExtension->isMethodSupported($methodReflection)) {
						continue;
					}

					return $dynamicMethodReturnTypeExtension->getTypeFromMethodCall($methodReflection, $node, $this);
				}

				foreach ($this->typeSpecifier->getMethodTypeSpecifyingExtensionsForClass($methodClassReflection->getName()) as $functionTypeSpecifyingExtension) {
					if (!$functionTypeSpecifyingExtension->isMethodSupported($methodReflection, $node, TypeSpecifierContext::createTruthy())) {
						continue;
					}

					$specifiedType = $this->findSpecifiedType($node, $methodReflection);
					if ($specifiedType !== null) {
						return $specifiedType;
					}
				}
			}

			if (!$methodCalledOnType->hasMethod($node->name)) {
				return new ErrorType();
			}
			$methodReflection = $methodCalledOnType->getMethod($node->name, $this);

			$calledOnThis = $node->var instanceof Variable && is_string($node->var->name) && $node->var->name === 'this';
			$methodReturnType = $methodReflection->getReturnType();
			if ($methodReturnType instanceof StaticResolvableType) {
				if ($calledOnThis) {
					if ($this->isInClass()) {
						return $methodReturnType->changeBaseClass($this->getClassReflection()->getName());
					}
				} elseif (count($referencedClasses) === 1) {
					return $methodReturnType->resolveStatic($referencedClasses[0]);
				}
			}

			return $methodReflection->getReturnType();
		}

		if ($node instanceof Expr\StaticCall && is_string($node->name)) {
			if ($node->class instanceof Name) {
				$calleeType = new ObjectType($this->resolveName($node->class));
			} else {
				$calleeType = $this->getType($node->class);
			}

			if (!$calleeType->hasMethod($node->name)) {
				return new ErrorType();
			}
			$staticMethodReflection = $calleeType->getMethod($node->name, $this);
			$referencedClasses = $calleeType->getReferencedClasses();
			if (
				count($calleeType->getReferencedClasses()) === 1
				&& $this->broker->hasClass($referencedClasses[0])
			) {
				$staticMethodClassReflection = $this->broker->getClass($referencedClasses[0]);
				foreach ($this->broker->getDynamicStaticMethodReturnTypeExtensionsForClass($staticMethodClassReflection->getName()) as $dynamicStaticMethodReturnTypeExtension) {
					if (!$dynamicStaticMethodReturnTypeExtension->isStaticMethodSupported($staticMethodReflection)) {
						continue;
					}

					return $dynamicStaticMethodReturnTypeExtension->getTypeFromStaticMethodCall($staticMethodReflection, $node, $this);
				}

				foreach ($this->typeSpecifier->getStaticMethodTypeSpecifyingExtensionsForClass($staticMethodClassReflection->getName()) as $functionTypeSpecifyingExtension) {
					if (!$functionTypeSpecifyingExtension->isStaticMethodSupported($staticMethodReflection, $node, TypeSpecifierContext::createTruthy())) {
						continue;
					}

					$specifiedType = $this->findSpecifiedType($node, $staticMethodReflection);
					if ($specifiedType !== null) {
						return $specifiedType;
					}
				}
			}
			if ($staticMethodReflection->getReturnType() instanceof StaticResolvableType) {
				if ($node->class instanceof Name) {
					$nodeClassString = strtolower((string) $node->class);
					if (in_array($nodeClassString, [
						'self',
						'static',
						'parent',
					], true) && $this->isInClass()) {
						return $staticMethodReflection->getReturnType()->changeBaseClass($this->getClassReflection()->getName());
					}
				}
				if (count($referencedClasses) === 1) {
					return $staticMethodReflection->getReturnType()->resolveStatic($referencedClasses[0]);
				}
			}
			return $staticMethodReflection->getReturnType();
		}

		if ($node instanceof PropertyFetch && is_string($node->name)) {
			$propertyFetchedOnType = $this->getType($node->var);
			if (!$propertyFetchedOnType->hasProperty($node->name)) {
				return new ErrorType();
			}

			return $propertyFetchedOnType->getProperty($node->name, $this)->getType();
		}

		if ($node instanceof Expr\StaticPropertyFetch && is_string($node->name) && $node->class instanceof Name) {
			$staticPropertyHolderClass = $this->resolveName($node->class);
			if ($this->broker->hasClass($staticPropertyHolderClass)) {
				$staticPropertyClassReflection = $this->broker->getClass(
					$staticPropertyHolderClass
				);
				if (!$staticPropertyClassReflection->hasProperty($node->name)) {
					return new ErrorType();
				}

				return $staticPropertyClassReflection->getProperty($node->name, $this)->getType();
			}
		}

		if ($node instanceof FuncCall) {
			if ($node->name instanceof Expr) {
				$calledOnType = $this->getType($node->name);
				if ($calledOnType->isCallable()->no()) {
					return new ErrorType();
				}

				return $calledOnType->getCallableParametersAcceptor($this)->getReturnType();
			}

			if (!$this->broker->hasFunction($node->name, $this)) {
				return new ErrorType();
			}

			$functionReflection = $this->broker->getFunction($node->name, $this);
			if ($functionReflection->getName() === 'is_a') {
				return new BooleanType();
			}

			if (
				$functionReflection->getName() === 'is_numeric'
				&& count($node->args) > 0
			) {
				$argType = $this->getType($node->args[0]->value);
				if ($argType instanceof ConstantScalarType) {
					return new ConstantBooleanType(
						!$argType->toNumber() instanceof ErrorType
					);
				}
				if (!(new StringType())->isSuperTypeOf($argType)->no()) {
					return new BooleanType();
				}
			}

			foreach ($this->broker->getDynamicFunctionReturnTypeExtensions() as $dynamicFunctionReturnTypeExtension) {
				if (!$dynamicFunctionReturnTypeExtension->isFunctionSupported($functionReflection)) {
					continue;
				}

				return $dynamicFunctionReturnTypeExtension->getTypeFromFunctionCall($functionReflection, $node, $this);
			}

			foreach ($this->typeSpecifier->getFunctionTypeSpecifyingExtensions() as $functionTypeSpecifyingExtension) {
				if (!$functionTypeSpecifyingExtension->isFunctionSupported($functionReflection, $node, TypeSpecifierContext::createTruthy())) {
					continue;
				}

				$specifiedType = $this->findSpecifiedType($node, $functionReflection);
				if ($specifiedType !== null) {
					return $specifiedType;
				}
			}

			return $functionReflection->getReturnType();
		}

		return new MixedType();
	}

	private function findSpecifiedType(
		Expr $node,
		ParametersAcceptor $parametersAcceptor
	): ?Type
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $node, TypeSpecifierContext::createTruthy());
		$sureTypes = $specifiedTypes->getSureTypes();
		$sureNotTypes = $specifiedTypes->getSureNotTypes();
		if (count($sureTypes) === 1) {
			$sureType = reset($sureTypes);
			$argumentType = $this->getType($sureType[0]);

			/** @var \PHPStan\Type\Type $resultType */
			$resultType = $sureType[1];

			$isSuperType = $resultType->isSuperTypeOf($argumentType);
			if ($isSuperType->yes()) {
				return new ConstantBooleanType(true);
			} elseif ($isSuperType->no()) {
				return new ConstantBooleanType(false);
			}

			return $parametersAcceptor->getReturnType();
		} elseif (count($sureNotTypes) === 1) {
			$sureNotType = reset($sureNotTypes);
			$argumentType = $this->getType($sureNotType[0]);

			/** @var \PHPStan\Type\Type $resultType */
			$resultType = $sureNotType[1];

			$isSuperType = $resultType->isSuperTypeOf($argumentType);
			if ($isSuperType->yes()) {
				return new ConstantBooleanType(false);
			} elseif ($isSuperType->no()) {
				return new ConstantBooleanType(true);
			}

			return $parametersAcceptor->getReturnType();
		} elseif (count($sureTypes) > 0) {
			$types = TypeCombinator::union(...array_map(function ($sureType) {
				return $sureType[1];
			}, array_values($sureTypes)));
			if ($types instanceof NeverType) {
				return new ConstantBooleanType(false);
			}
		} elseif (count($sureNotTypes) > 0) {
			$types = TypeCombinator::union(...array_map(function ($sureNotType) {
				return $sureNotType[1];
			}, array_values($sureNotTypes)));
			if ($types instanceof NeverType) {
				return new ConstantBooleanType(true);
			}
		}

		return null;
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
			$array = new ConstantArrayType([], []);
			foreach ($value as $k => $v) {
				$array = $array->setOffsetValueType($this->getTypeFromValue($k), $this->getTypeFromValue($v));
			}
			return $array;
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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
	 * @param null|Type $phpDocReturnType
	 * @return self
	 */
	public function enterClassMethod(
		Node\Stmt\ClassMethod $classMethod,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType
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
				$phpDocReturnType
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
			$realParameterTypes[$parameter->name] = $this->getFunctionType(
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
	 * @param null|Type $phpDocReturnType
	 * @return self
	 */
	public function enterFunction(
		Node\Stmt\Function_ $function,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType = null
	): self
	{
		return $this->enterFunctionLike(
			new PhpFunctionFromParserNodeReflection(
				$function,
				$this->getRealParameterTypes($function),
				$phpDocParameterTypes,
				$function->returnType !== null,
				$this->getFunctionType($function->returnType, $function->returnType === null, false),
				$phpDocReturnType
			)
		);
	}

	/**
	 * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionReflection
	 * @return self
	 */
	private function enterFunctionLike($functionReflection): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($functionReflection->getParameters() as $parameter) {
			$variableTypes[$parameter->getName()] = VariableTypeHolder::createYes($parameter->getType());
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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

	public function isInClosureBind(): bool
	{
		return $this->inClosureBindScopeClass !== null;
	}

	public function enterAnonymousClass(ClassReflection $anonymousClass): self
	{
		return $this->enterClass($anonymousClass);
	}

	/**
	 * @param \PhpParser\Node\Param[] $parameters
	 * @param \PhpParser\Node\Expr\ClosureUse[] $uses
	 * @param \PhpParser\Node\Name|string|\PhpParser\Node\NullableType|null $returnTypehint
	 * @return self
	 */
	public function enterAnonymousFunction(
		array $parameters,
		array $uses,
		$returnTypehint = null
	): self
	{
		$variableTypes = [];
		foreach ($parameters as $parameter) {
			$isNullable = $this->isParameterValueNullable($parameter);

			$variableTypes[$parameter->name] = VariableTypeHolder::createYes(
				$this->getFunctionType($parameter->type, $isNullable, $parameter->variadic)
			);
		}

		foreach ($uses as $use) {
			if ($this->hasVariableType($use->var)->no()) {
				if ($use->byRef) {
					$variableTypes[$use->var] = VariableTypeHolder::createYes(new NullType());
				}
				continue;
			}
			$variableTypes[$use->var] = VariableTypeHolder::createYes($this->getVariableType($use->var));
		}

		if ($this->hasVariableType('this')->yes()) {
			$variableTypes['this'] = VariableTypeHolder::createYes($this->getVariableType('this'));
		}

		$returnType = $this->getFunctionType($returnTypehint, $returnTypehint === null, false);

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
	 * @param \PhpParser\Node\Name|string|\PhpParser\Node\NullableType|null $type
	 * @param bool $isNullable
	 * @param bool $isVariadic
	 * @return Type
	 */
	public function getFunctionType($type = null, bool $isNullable, bool $isVariadic): Type
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
			), false);
		}
		if ($type === null) {
			return new MixedType();
		} elseif ($type === 'string') {
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
		} elseif ($type === 'iterable') {
			return new IterableType(new MixedType(), new MixedType());
		} elseif ($type === 'void') {
			return new VoidType();
		} elseif ($type === 'object') {
			return new ObjectWithoutClassType();
		} elseif ($type instanceof Node\NullableType) {
			return $this->getFunctionType($type->type, true, $isVariadic);
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
		$type = TypeCombinator::union(...array_map(function (string $class): ObjectType {
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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		$moreSpecificTypes = $this->moreSpecificTypes;
		foreach ($moreSpecificTypes as $key => $type) {
			$matches = \Nette\Utils\Strings::match($key, '#^(\$[a-zA-Z_][a-zA-Z_0-9]*)#');
			if ($matches === null) {
				continue;
			}

			if ($matches[1] !== $variableString) {
				continue;
			}

			unset($moreSpecificTypes[$key]);
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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

			return new self(
				$this->broker,
				$this->printer,
				$this->typeSpecifier,
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
			$arrayType = $this->getType($expr->var);
			$dimType = $this->getType($expr->dim);
			if ($arrayType instanceof ConstantArrayType) {
				return $this->specifyExpressionType(
					$expr->var,
					$arrayType->unsetOffset($dimType)
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

		$theirSpecifiedTypes = $otherScope->moreSpecificTypes;
		$intersectedSpecifiedTypes = [];
		foreach ($this->moreSpecificTypes as $exprString => $specificType) {
			if (!isset($theirSpecifiedTypes[$exprString])) {
				continue;
			}

			$intersectedSpecifiedTypes[$exprString] = TypeCombinator::union(
				$specificType,
				$theirSpecifiedTypes[$exprString]
			);
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		foreach ($otherScope->moreSpecificTypes as $exprString => $specificType) {
			$specifiedTypes[$exprString] = $specificType;
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		$specifiedTypes = $this->moreSpecificTypes;
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

			$variableTypeHolders[$name] = $theirVariableTypeHolder->addMaybe();

			$exprString = $this->printer->prettyPrintExpr(new Variable($name));
			unset($specifiedTypes[$exprString]);
		}

		foreach ($intersectedScope->moreSpecificTypes as $exprString => $specificType) {
			if (preg_match('#^\$([a-zA-Z_]\w*)$#', (string) $exprString, $matches) === 1) {
				$variableName = $matches[1];
				$variableTypeHolders[$variableName] = VariableTypeHolder::createYes($specificType);
				continue;
			}
			$specifiedTypes[$exprString] = $specificType;
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypeHolders,
			$specifiedTypes,
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
					&& $theirVariableTypeHolder->getType()->isSuperTypeOf($ourVariableTypeHolders[$name]->getType())->and($ourVariableTypeHolders[$name]->getType()->isSuperTypeOf($theirVariableTypeHolder->getType()))->yes()
					&& $ourVariableTypeHolders[$name]->getCertainty()->equals($theirVariableTypeHolder->getCertainty())
				) {
					unset($ourVariableTypeHolders[$name]);
				}
			}
		}

		$moreSpecificTypes = $this->moreSpecificTypes;
		foreach ($otherScope->moreSpecificTypes as $exprString => $specifiedType) {
			if (!isset($moreSpecificTypes[$exprString]) || !$specifiedType->isSuperTypeOf($moreSpecificTypes[$exprString])->and($moreSpecificTypes[$exprString]->isSuperTypeOf($specifiedType))->yes()) {
				continue;
			}

			unset($moreSpecificTypes[$exprString]);
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$ourVariableTypeHolders,
			$moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function specifyExpressionType(Expr $expr, Type $type): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);

		if ($expr instanceof Variable && is_string($expr->name)) {
			$variableName = $expr->name;

			$variableTypes = $this->getVariableTypes();
			$variableTypes[$variableName] = VariableTypeHolder::createYes($type);

			$moreSpecificTypes = $this->moreSpecificTypes;
			$moreSpecificTypes[$exprString] = $type;

			return new self(
				$this->broker,
				$this->printer,
				$this->typeSpecifier,
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
			$arrayType = $this->getType($expr->var);
			if ($arrayType instanceof ConstantArrayType) {
				$dimType = $this->getType($expr->dim);
				$arrayType = $arrayType->setOffsetValueType($dimType, $type);
				return $this->specifyExpressionType($expr->var, $arrayType);
			}
		}

		return $this->addMoreSpecificTypes([
			$exprString => $type,
		]);
	}

	public function unspecifyExpressionType(Expr $expr): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);
		$moreSpecificTypes = $this->moreSpecificTypes;
		if (isset($moreSpecificTypes[$exprString]) && !$moreSpecificTypes[$exprString] instanceof MixedType) {
			unset($moreSpecificTypes[$exprString]);
			return new self(
				$this->broker,
				$this->printer,
				$this->typeSpecifier,
				$this->context,
				$this->isDeclareStrictTypes(),
				$this->getFunction(),
				$this->getNamespace(),
				$this->getVariableTypes(),
				$moreSpecificTypes,
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
		$scope = $this;
		foreach ($specifiedTypes->getSureTypes() as list($expr, $type)) {
			$type = TypeCombinator::intersect($type, $this->getType($expr));
			$scope = $scope->specifyExpressionType($expr, $type);
		}
		foreach ($specifiedTypes->getSureNotTypes() as list($expr, $type)) {
			$scope = $scope->removeTypeFromExpression($expr, $type);
		}
		return $scope;
	}

	public function specifyFetchedPropertyFromIsset(PropertyFetch $expr): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);

		return $this->addMoreSpecificTypes([
			$exprString => new MixedType(),
		]);
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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
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
		$moreSpecificTypes = $this->moreSpecificTypes;
		foreach ($types as $exprString => $type) {
			$moreSpecificTypes[$exprString] = $type;
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->context,
			$this->isDeclareStrictTypes(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$moreSpecificTypes,
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

	public function canAccessConstant(ClassConstantReflection $constantReflection): bool
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
		foreach ($this->moreSpecificTypes as $exprString => $type) {
			$key = $exprString;
			if (isset($descriptions[$key])) {
				$key .= '-specified';
			}
			$descriptions[$key] = $type->describe(VerbosityLevel::value());
		}

		return $descriptions;
	}

}
