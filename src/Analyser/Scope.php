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
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableIterableType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\TrueOrFalseBooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VoidType;
use PHPStan\TypeX\ConstantScalarType;
use PHPStan\TypeX\ConstantStringType;
use PHPStan\TypeX\Is;
use PHPStan\TypeX\TypeX;
use PHPStan\TypeX\TypeXFactory;

class Scope
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\TypeX\TypeXFactory
	 */
	private $typeFactory;

	/**
	 * @var \PhpParser\PrettyPrinter\Standard
	 */
	private $printer;

	/**
	 * @var \PHPStan\Analyser\TypeSpecifier
	 */
	private $typeSpecifier;

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var \PHPStan\Type\Type[]
	 */
	private $resolvedTypes = [];

	/**
	 * @var string
	 */
	private $analysedContextFile;

	/**
	 * @var bool
	 */
	private $declareStrictTypes;

	/**
	 * @var \PHPStan\Reflection\ClassReflection|null
	 */
	private $classReflection;

	/**
	 * @var \PHPStan\Reflection\ParametersAcceptor|null
	 */
	private $function;

	/**
	 * @var string|null
	 */
	private $namespace;

	/**
	 * @var \PHPStan\Type\Type[]
	 */
	private $variableTypes;

	/**
	 * @var string|null
	 */
	private $inClosureBindScopeClass;

	/**
	 * @var \PHPStan\Type\Type|null
	 */
	private $inAnonymousFunctionReturnType;

	/**
	 * @var \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
	 */
	private $inFunctionCall;

	/**
	 * @var bool
	 */
	private $negated;

	/**
	 * @var \PHPStan\Type\Type[]
	 */
	private $moreSpecificTypes;

	/**
	 * @var bool
	 */
	private $inFirstLevelStatement;

	/**
	 * @var string[]
	 */
	private $currentlyAssignedVariables = [];

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Analyser\TypeSpecifier $typeSpecifier
	 * @param string $file
	 * @param string|null $analysedContextFile
	 * @param bool $declareStrictTypes
	 * @param \PHPStan\Reflection\ClassReflection|null $classReflection
	 * @param \PHPStan\Reflection\ParametersAcceptor|null $function
	 * @param string|null $namespace
	 * @param \PHPStan\Type\Type[] $variablesTypes
	 * @param string|null $inClosureBindScopeClass
	 * @param \PHPStan\Type\Type|null $inAnonymousFunctionReturnType
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null $inFunctionCall
	 * @param bool $negated
	 * @param \PHPStan\Type\Type[] $moreSpecificTypes
	 * @param bool $inFirstLevelStatement
	 * @param string[] $currentlyAssignedVariables
	 */
	public function __construct(
		Broker $broker,
		\PhpParser\PrettyPrinter\Standard $printer,
		TypeSpecifier $typeSpecifier,
		string $file,
		string $analysedContextFile = null,
		bool $declareStrictTypes = false,
		ClassReflection $classReflection = null,
		\PHPStan\Reflection\ParametersAcceptor $function = null,
		string $namespace = null,
		array $variablesTypes = [],
		string $inClosureBindScopeClass = null,
		Type $inAnonymousFunctionReturnType = null,
		Expr $inFunctionCall = null,
		bool $negated = false,
		array $moreSpecificTypes = [],
		bool $inFirstLevelStatement = true,
		array $currentlyAssignedVariables = []
	)
	{
		if ($namespace === '') {
			$namespace = null;
		}

		$this->broker = $broker;
		$this->typeFactory = $this->broker->getTypeFactory();
		$this->printer = $printer;
		$this->typeSpecifier = $typeSpecifier;
		$this->file = $file;
		$this->analysedContextFile = $analysedContextFile !== null ? $analysedContextFile : $file;
		$this->declareStrictTypes = $declareStrictTypes;
		$this->classReflection = $classReflection;
		$this->function = $function;
		$this->namespace = $namespace;
		$this->variableTypes = $variablesTypes;
		$this->inClosureBindScopeClass = $inClosureBindScopeClass;
		$this->inAnonymousFunctionReturnType = $inAnonymousFunctionReturnType;
		$this->inFunctionCall = $inFunctionCall;
		$this->negated = $negated;
		$this->moreSpecificTypes = $moreSpecificTypes;
		$this->inFirstLevelStatement = $inFirstLevelStatement;
		$this->currentlyAssignedVariables = $currentlyAssignedVariables;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	public function getAnalysedContextFile(): string
	{
		return $this->analysedContextFile;
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			true
		);
	}

	public function isInClass(): bool
	{
		return $this->classReflection !== null;
	}

	public function getClassReflection(): ClassReflection
	{
		/** @var \PHPStan\Reflection\ClassReflection $classReflection */
		$classReflection = $this->classReflection;
		return $classReflection;
	}

	/**
	 * @return null|\PHPStan\Reflection\ParametersAcceptor
	 */
	public function getFunction()
	{
		return $this->function;
	}

	/**
	 * @return null|string
	 */
	public function getFunctionName()
	{
		return $this->function !== null ? $this->function->getName() : null;
	}

	/**
	 * @return null|string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @return \PHPStan\Type\Type[]
	 */
	public function getVariableTypes(): array
	{
		return $this->variableTypes;
	}

	public function hasVariableType(string $variableName): bool
	{
		return isset($this->variableTypes[$variableName]);
	}

	public function getVariableType(string $variableName): Type
	{
		if (!$this->hasVariableType($variableName)) {
			throw new \PHPStan\Analyser\UndefinedVariableException($this, $variableName);
		}

		return $this->variableTypes[$variableName];
	}

	public function isInAnonymousFunction(): bool
	{
		return $this->inAnonymousFunctionReturnType !== null;
	}

	/**
	 * @return \PHPStan\Type\Type|null
	 */
	public function getAnonymousFunctionReturnType()
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
			$node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr
			|| $node instanceof \PhpParser\Node\Expr\BooleanNot
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalXor
			|| $node instanceof Expr\BinaryOp\Greater
			|| $node instanceof Expr\BinaryOp\GreaterOrEqual
			|| $node instanceof Expr\BinaryOp\Smaller
			|| $node instanceof Expr\BinaryOp\SmallerOrEqual
		) {
			return $this->typeFactory->createBooleanType();
		}

		if (
			$node instanceof Node\Expr\UnaryMinus
			|| $node instanceof Node\Expr\UnaryPlus
		) {
			return $this->getType($node->expr);
		}

		if ($node instanceof Node\Expr\BinaryOp\Mod) {
			return $this->typeFactory->createIntegerType();
		}

		if ($node instanceof Expr\BinaryOp\Concat) {
			return $this->typeFactory->createStringType();
		}

		if ($node instanceof Expr\BinaryOp\Spaceship) {
			return $this->typeFactory->createIntegerType();
		}

		if ($node instanceof Expr\Ternary) {
			$elseType = $this->getType($node->else);
			if ($node->if === null) {
				return $this->typeFactory->createUnionType(
					$this->typeFactory->createIntersectionType(
						$this->typeFactory->createFromLegacy($this->getType($node->cond)),
						$this->typeFactory->createComplementType($this->typeFactory->createNullType())
					),
					$this->typeFactory->createFromLegacy($elseType)
				);
			}

			$conditionScope = $this->lookForTypeSpecifications($node->cond);
			$ifType = $conditionScope->getType($node->if);
			$negatedConditionScope = $this->lookForTypeSpecificationsInEarlyTermination($node->cond);
			$elseType = $negatedConditionScope->getType($node->else);

			return $ifType->combineWith($elseType);
		}

		if ($node instanceof Expr\BinaryOp\Coalesce) {
			return $this->typeFactory->createUnionType(
				$this->typeFactory->createIntersectionType(
					$this->typeFactory->createFromLegacy($this->getType($node->left)),
					$this->typeFactory->createComplementType($this->typeFactory->createNullType())
				),
				$this->typeFactory->createFromLegacy($this->getType($node->right))
			);
		}

		if ($node instanceof Expr\Clone_) {
			return $this->getType($node->expr);
		}

		if (
			$node instanceof Node\Expr\BinaryOp\Plus
			|| $node instanceof Node\Expr\BinaryOp\Minus
			|| $node instanceof Node\Expr\BinaryOp\Mul
			|| $node instanceof Node\Expr\BinaryOp\Pow
			|| $node instanceof Node\Expr\BinaryOp\Div
			|| $node instanceof Node\Expr\AssignOp
		) {
			if ($node instanceof Node\Expr\AssignOp) {
				$left = $node->var;
				$right = $node->expr;
			} elseif ($node instanceof Node\Expr\BinaryOp) {
				$left = $node->left;
				$right = $node->right;
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$leftType = $this->getType($left);
			$rightType = $this->getType($right);

			if (Is::type($leftType, BooleanType::class)) {
				$leftType = $this->typeFactory->createIntegerType();
			}

			if (Is::type($rightType, BooleanType::class)) {
				$rightType = $this->typeFactory->createIntegerType();
			}

			if ($node instanceof Expr\AssignOp\Div || $node instanceof Expr\BinaryOp\Div) {
				if ($leftType instanceof ConstantScalarType && $rightType instanceof ConstantScalarType) {
					return $this->getTypeFromValue($leftType->getValue() / $rightType->getValue());
				}

				if (Is::type($leftType, FloatType::class) || Is::type($rightType, FloatType::class)) {
					return $this->typeFactory->createFloatType();
				}

				return $this->typeFactory->createUnionType(
					$this->typeFactory->createIntegerType(),
					$this->typeFactory->createFloatType()
				);
			}

			if (Is::type($leftType, IntegerType::class) && Is::type($rightType, IntegerType::class)) {
				return $this->typeFactory->createIntegerType(); // technically not true because integer may overflow to float
			}

			if (Is::type($leftType, FloatType::class) || Is::type($rightType, FloatType::class)) {
				return $this->typeFactory->createFloatType();
			}

			return $this->typeFactory->createUnionType(
				$this->typeFactory->createIntegerType(),
				$this->typeFactory->createFloatType()
			);
		}

		if ($node instanceof LNumber) {
			return $this->typeFactory->createConstantIntegerType($node->value);
		} elseif ($node instanceof ConstFetch) {
			$constName = strtolower((string) $node->name);
			if ($constName === 'true') {
				return $this->typeFactory->createTrueType();
			} elseif ($constName === 'false') {
				return $this->typeFactory->createFalseType();
			} elseif ($constName === 'null') {
				return $this->typeFactory->createNullType();
			}
		} elseif ($node instanceof String_) {
			return $this->typeFactory->createConstantStringType($node->value);
		} elseif ($node instanceof DNumber) {
			return $this->typeFactory->createConstantFloatType($node->value);
		} elseif ($node instanceof Expr\Closure) {
			return $this->typeFactory->createObjectType(\Closure::class);
		} elseif ($node instanceof New_) {
			if ($node->class instanceof Name) {
				if (
					count($node->class->parts) === 1
				) {
					if ($node->class->parts[0] === 'static') {
						return $this->typeFactory->createStaticType($this->getClassReflection()->getName());
					} elseif ($node->class->parts[0] === 'self') {
						return $this->typeFactory->createObjectType($this->getClassReflection()->getName());
					}
				}

				return $this->typeFactory->createObjectType((string) $node->class);
			}
		} elseif ($node instanceof Array_) {
			$keyTypes = [];
			$valueType = [];
			$nextIndex = 0;
			foreach ($node->items as $arrayItem) {
				if ($arrayItem->key === null) {
					if ($nextIndex !== null) {
						$keyTypes[] = $this->typeFactory->createConstantIntegerType($nextIndex++);
					} else {
						$keyTypes[] = $this->typeFactory->createIntegerType();
					}
				} else {
					if ($arrayItem->key instanceof LNumber) {
						$nextIndex = max($nextIndex, $arrayItem->key->value + 1);
					} else {
						$nextIndex = null;
					}
					$keyTypes[] = $this->typeFactory->createFromLegacy($this->getType($arrayItem->key));
				}
				$valueType[] = $this->typeFactory->createFromLegacy($this->getType($arrayItem->value));
			}
			return $this->typeFactory->createConstantArrayType($keyTypes, $valueType);
		} elseif ($node instanceof Int_) {
				return $this->typeFactory->createIntegerType();
		} elseif ($node instanceof Bool_) {
			return $this->typeFactory->createBooleanType();
		} elseif ($node instanceof Double) {
			return $this->typeFactory->createFloatType();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
			return $this->typeFactory->createStringType();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
			return $this->typeFactory->createArrayType();
		} elseif ($node instanceof Node\Scalar\MagicConst\Line) {
			return $this->typeFactory->createConstantIntegerType($node->getLine());
		} elseif ($node instanceof Node\Scalar\MagicConst\Class_) {
			return $this->typeFactory->createConstantStringType($this->classReflection->getName());
		} elseif ($node instanceof Node\Scalar\MagicConst) {
			return $this->typeFactory->createStringType();
		} elseif ($node instanceof Object_) {
			return $this->typeFactory->createObjectType(\stdClass::class);
		} elseif ($node instanceof Unset_) {
			return $this->typeFactory->createNullType();
		} elseif ($node instanceof Node\Expr\ClassConstFetch) {
			if ($node->class instanceof Name) {
				$constantClass = (string) $node->class;
				if ($constantClass === 'self') {
					$constantClass = $this->getClassReflection()->getName();
				}
			} elseif ($node->class instanceof Expr) {
				$constantClassType = $this->getType($node->class);
				if ($constantClassType->getClass() !== null) {
					$constantClass = $constantClassType->getClass();
				}
			}

			if (isset($constantClass) && is_string($node->name)) {
				$constantName = $node->name;
				if (strtolower($constantName) === 'class') {
					return $this->typeFactory->createConstantStringType($constantClass);
				}
				if ($this->broker->hasClass($constantClass)) {
					$constantClassReflection = $this->broker->getClass($constantClass);
					if ($constantClassReflection->hasConstant($constantName)) {
						$constant = $constantClassReflection->getConstant($constantName);
						$typeFromValue = $this->getTypeFromValue($constant->getValue());
						if ($typeFromValue !== null) {
							return $typeFromValue;
						}
					}
				}
			}
		}

		if ($node instanceof Variable && is_string($node->name)) {
			if (!$this->hasVariableType($node->name)) {
				return $this->typeFactory->createMixedType();
			}

			return $this->getVariableType($node->name);
		}

		$exprString = $this->printer->prettyPrintExpr($node);
		if (isset($this->moreSpecificTypes[$exprString])) {
			return $this->moreSpecificTypes[$exprString];
		}

		if ($node instanceof Expr\ArrayDimFetch && $node->dim !== null) {
			$arrayType = $this->getType($node->var);
			if ($arrayType instanceof TypeX) {
				return $arrayType->getOffsetValueType($this->typeFactory->createFromLegacy($this->getType($node->dim)));
			} elseif ($arrayType instanceof ArrayType) {
				return $arrayType->getItemType();
			}
		}

		if ($node instanceof MethodCall && is_string($node->name)) {
			$methodCalledOnType = $this->getType($node->var);
			if (
				$methodCalledOnType->getClass() !== null
				&& $this->broker->hasClass($methodCalledOnType->getClass())
			) {
				$methodClassReflection = $this->broker->getClass(
					$methodCalledOnType->getClass()
				);
				if (!$methodClassReflection->hasMethod($node->name)) {
					return $this->typeFactory->createMixedType();
				}

				$methodReflection = $methodClassReflection->getMethod($node->name, $this);
				foreach ($this->broker->getDynamicMethodReturnTypeExtensionsForClass($methodCalledOnType->getClass()) as $dynamicMethodReturnTypeExtension) {
					if (!$dynamicMethodReturnTypeExtension->isMethodSupported($methodReflection)) {
						continue;
					}

					return $dynamicMethodReturnTypeExtension->getTypeFromMethodCall($methodReflection, $node, $this);
				}

				$calledOnThis = $node->var instanceof Variable && is_string($node->var->name) && $node->var->name === 'this';
				if (!$calledOnThis && $methodReflection->getReturnType() instanceof StaticResolvableType) {
					return $methodReflection->getReturnType()->resolveStatic($methodCalledOnType->getClass());
				}

				return $methodReflection->getReturnType();
			}
		}

		if ($node instanceof Expr\StaticCall && is_string($node->name) && $node->class instanceof Name) {
			$calleeClass = $this->resolveName($node->class);

			if ($this->broker->hasClass($calleeClass)) {
				$staticMethodClassReflection = $this->broker->getClass($calleeClass);
				if (!$staticMethodClassReflection->hasMethod($node->name)) {
					return $this->typeFactory->createMixedType();
				}
				$staticMethodReflection = $staticMethodClassReflection->getMethod($node->name, $this);
				foreach ($this->broker->getDynamicStaticMethodReturnTypeExtensionsForClass($calleeClass) as $dynamicStaticMethodReturnTypeExtension) {
					if (!$dynamicStaticMethodReturnTypeExtension->isStaticMethodSupported($staticMethodReflection)) {
						continue;
					}

					return $dynamicStaticMethodReturnTypeExtension->getTypeFromStaticMethodCall($staticMethodReflection, $node, $this);
				}
				if ($staticMethodReflection->getReturnType() instanceof StaticResolvableType) {
					$nodeClassString = (string) $node->class;
					if ($nodeClassString === 'parent' && $this->isInClass()) {
						return $staticMethodReflection->getReturnType()->changeBaseClass($this->getClassReflection()->getName());
					}

					return $staticMethodReflection->getReturnType()->resolveStatic($calleeClass);
				}
				return $staticMethodReflection->getReturnType();
			}
		}

		if ($node instanceof PropertyFetch && is_string($node->name)) {
			$propertyFetchedOnType = $this->getType($node->var);
			if (
				$propertyFetchedOnType->getClass() !== null
				&& $this->broker->hasClass($propertyFetchedOnType->getClass())
			) {
				$propertyClassReflection = $this->broker->getClass(
					$propertyFetchedOnType->getClass()
				);
				if (!$propertyClassReflection->hasProperty($node->name)) {
					return $this->typeFactory->createMixedType();
				}

				return $propertyClassReflection->getProperty($node->name, $this)->getType();
			}
		}

		if ($node instanceof Expr\StaticPropertyFetch && is_string($node->name) && $node->class instanceof Name) {
			$staticPropertyHolderClass = $this->resolveName($node->class);
			if ($this->broker->hasClass($staticPropertyHolderClass)) {
				$staticPropertyClassReflection = $this->broker->getClass(
					$staticPropertyHolderClass
				);
				if (!$staticPropertyClassReflection->hasProperty($node->name)) {
					return $this->typeFactory->createMixedType();
				}

				return $staticPropertyClassReflection->getProperty($node->name, $this)->getType();
			}
		}

		if ($node instanceof FuncCall && $node->name instanceof Name) {
			$arrayFunctionsThatDependOnClosureReturnType = [
				'array_map' => 0,
				'array_reduce' => 1,
			];
			$functionName = strtolower((string) $node->name);
			if (
				isset($arrayFunctionsThatDependOnClosureReturnType[$functionName])
				&& isset($node->args[$arrayFunctionsThatDependOnClosureReturnType[$functionName]])
				&& $node->args[$arrayFunctionsThatDependOnClosureReturnType[$functionName]]->value instanceof Expr\Closure
			) {
				$closure = $node->args[$arrayFunctionsThatDependOnClosureReturnType[$functionName]]->value;
				$anonymousFunctionType = $this->getFunctionType($closure->returnType, $closure->returnType === null, false);
				if ($functionName === 'array_reduce') {
					return $anonymousFunctionType;
				}

				return $this->typeFactory->createArrayType(null, $this->typeFactory->createFromLegacy($anonymousFunctionType), true);
			}

			$arrayFunctionsThatDependOnArgumentType = [
				'array_filter' => 0,
				'array_unique' => 0,
				'array_reverse' => 0,
			];
			if (
				isset($arrayFunctionsThatDependOnArgumentType[$functionName])
				&& isset($node->args[$arrayFunctionsThatDependOnArgumentType[$functionName]])
			) {
				$argumentValue = $node->args[$arrayFunctionsThatDependOnArgumentType[$functionName]]->value;
				return $this->getType($argumentValue);
			}

			$arrayFunctionsThatCreateArrayBasedOnArgumentType = [
				'array_fill' => 2,
				'array_fill_keys' => 1,
			];
			if (
				isset($arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName])
				&& isset($node->args[$arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName]])
			) {
				$argumentValue = $node->args[$arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName]]->value;
				return $this->typeFactory->createArrayType(
					null,
					$this->typeFactory->createFromLegacy($this->getType($argumentValue)),
					true
				);
			}

			$functionsThatCombineAllArgumentTypes = [
				'min' => '',
				'max' => '',
			];
			if (
				isset($functionsThatCombineAllArgumentTypes[$functionName])
				&& isset($node->args[0])
			) {
				if ($node->args[0]->unpack) {
					$argumentType = $this->getType($node->args[0]->value);
					if (Is::type($argumentType, ArrayType::class)) {
						return $argumentType->getItemType();
					}
				}

				if (count($node->args) === 1) {
					$argumentType = $this->getType($node->args[0]->value);
					if (Is::type($argumentType, ArrayType::class)) {
						return $argumentType->getItemType();
					}
				}

				$argumentType = null;
				foreach ($node->args as $arg) {
					$argType = $this->getType($arg->value);
					if ($argumentType === null) {
						$argumentType = $argType;
					} else {
						$argumentType = $argumentType->combineWith($argType);
					}
				}

				return $argumentType;
			}

			if (!$this->broker->hasFunction($node->name, $this)) {
				return $this->typeFactory->createMixedType();
			}

			return $this->typeFactory->createFromLegacy(
				$this->broker->getFunction($node->name, $this)->getReturnType()
			);
		}

		return $this->typeFactory->createMixedType();
	}

	public function resolveName(Name $name): string
	{
		$originalClass = (string) $name;
		if ($originalClass === 'self' || $originalClass === 'static') {
			return $this->getClassReflection()->getName();
		} elseif ($originalClass === 'parent' && $this->isInClass()) {
			$currentClassReflection = $this->getClassReflection();
			if ($currentClassReflection->getParentClass() !== false) {
				return $currentClassReflection->getParentClass()->getName();
			}
		}

		return $originalClass;
	}

	/**
	 * @param mixed $value
	 * @return TypeX|null
	 */
	private function getTypeFromValue($value)
	{
		if (is_int($value)) {
			return $this->typeFactory->createConstantIntegerType($value);
		} elseif (is_float($value)) {
			return $this->typeFactory->createConstantFloatType($value);
		} elseif ($value === true) {
			return $this->typeFactory->createTrueType();
		} elseif ($value === false) {
			return $this->typeFactory->createFalseType();
		} elseif ($value === null) {
			return $this->typeFactory->createNullType();
		} elseif (is_string($value)) {
			return $this->typeFactory->createConstantStringType($value);
		} elseif (is_array($value)) {
			$callback = function ($value) {
				return $this->getTypeFromValue($value) ?? $this->typeFactory->createMixedType();
			};
			return $this->typeFactory->createConstantArrayType(
				array_map($callback, array_keys($value)),
				array_map($callback, array_values($value))
			);
		}

		return null;
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$classReflection,
			null,
			$this->getNamespace(),
			[
				'this' => $this->typeFactory->createThisType($classReflection->getName())
			]
		);
	}

	public function changeAnalysedContextFile(string $fileName): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$fileName,
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes
		);
	}

	public function enterClassMethod(
		Node\Stmt\ClassMethod $classMethod,
		array $phpDocParameterTypes,
		Type $phpDocReturnType = null
	): self
	{
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

	public function enterFunction(
		Node\Stmt\Function_ $function,
		array $phpDocParameterTypes,
		Type $phpDocReturnType = null
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

	private function enterFunctionLike(ParametersAcceptor $functionReflection): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($functionReflection->getParameters() as $parameter) {
			$variableTypes[$parameter->getName()] = $parameter->getType();
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$functionReflection,
			$this->getNamespace(),
			$variableTypes,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			null,
			null,
			$namespaceName
		);
	}

	public function enterClosureBind(Type $thisType = null, string $scopeClass): self
	{
		$variableTypes = $this->getVariableTypes();

		if ($thisType !== null) {
			$variableTypes['this'] = $thisType;
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$scopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes
		);
	}

	public function enterAnonymousClass(ClassReflection $anonymousClass): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$anonymousClass,
			null,
			$this->getNamespace(),
			[
				'this' => $this->typeFactory->createMixedType(),
			],
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall()
		);
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

			$variableTypes[$parameter->name] = $this->getFunctionType($parameter->type, $isNullable, $parameter->variadic);
		}

		foreach ($uses as $use) {
			if (!$this->hasVariableType($use->var)) {
				if ($use->byRef) {
					$variableTypes[$use->var] = $this->typeFactory->createMixedType();
				}
				continue;
			}
			$variableTypes[$use->var] = $this->getVariableType($use->var);
		}

		if ($this->hasVariableType('this')) {
			$variableTypes['this'] = $this->getVariableType('this');
		}

		$returnType = $this->getFunctionType($returnTypehint, $returnTypehint === null, false);

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$returnType,
			$this->getInFunctionCall()
		);
	}

	private function isParameterValueNullable(Node\Param $parameter): bool
	{
		if ($parameter->default instanceof ConstFetch && $parameter->default->name instanceof Name) {
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
	private function getFunctionType($type = null, bool $isNullable, bool $isVariadic): Type
	{
		if ($isNullable) {
			return $this->typeFactory->createUnionType(
				$this->getFunctionType($type, false, $isVariadic),
				$this->typeFactory->createNullType()
			);
		}

		if ($isVariadic) {
			return $this->typeFactory->createArrayType(
				$this->typeFactory->createIntegerType(),
				$this->getFunctionType($type, false, false)
			);
		}

		if ($type === null) {
			return $this->typeFactory->createMixedType();
		} elseif ($type === 'string') {
			return $this->typeFactory->createStringType();
		} elseif ($type === 'int') {
			return $this->typeFactory->createIntegerType();
		} elseif ($type === 'bool') {
			return $this->typeFactory->createBooleanType();
		} elseif ($type === 'float') {
			return $this->typeFactory->createFloatType();
		} elseif ($type === 'callable') {
			return $this->typeFactory->createCallableType();
		} elseif ($type === 'array') {
			return $this->typeFactory->createArrayType();
		} elseif ($type instanceof Name) {
			$className = (string) $type;
			if ($className === 'self') {
				$className = $this->getClassReflection()->getName();
			} elseif (
				$className === 'parent'
			) {
				if ($this->isInClass() && $this->getClassReflection()->getParentClass() !== false) {
					return $this->typeFactory->createObjectType($this->getClassReflection()->getParentClass()->getName());
				}

				return new NonexistentParentClassType(); // TODO!
			}
			return $this->typeFactory->createObjectType($className);
		} elseif ($type === 'iterable') {
			return $this->typeFactory->createIterableType();
		} elseif ($type === 'void') {
			return $this->typeFactory->createVoidType();
		} elseif ($type instanceof Node\NullableType) {
			return $this->getFunctionType($type->type, true, $isVariadic);
		}

		return $this->typeFactory->createMixedType();
	}

	public function enterForeach(Expr $iteratee, string $valueName, string $keyName = null): self
	{
		$iterateeType = $this->getType($iteratee);
		$variableTypes = $this->getVariableTypes();
		if ($iterateeType instanceof TypeX) {
			$variableTypes[$valueName] = $iterateeType->getIterableValueType();
		} elseif ($iterateeType instanceof IterableType) {
			$variableTypes[$valueName] = $iterateeType->getItemType();
		} else {
			$variableTypes[$valueName] = $this->typeFactory->createMixedType();
		}

		if ($keyName !== null) {
			$variableTypes[$keyName] = $this->typeFactory->createMixedType();
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			null,
			$this->isNegated(),
			$this->moreSpecificTypes
		);
	}

	/**
	 * @param \PhpParser\Node\Name[] $classes
	 * @param string $variableName
	 * @return Scope
	 */
	public function enterCatch(array $classes, string $variableName): self
	{
		$variableTypes = $this->getVariableTypes();

		$types = array_map(
			function ($class) {
				return $this->typeFactory->createObjectType((string) $class);
			},
			$classes
		);

		$variableTypes[$variableName] = $this->typeFactory->createUnionType(...$types);

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			null,
			$this->isNegated(),
			$this->moreSpecificTypes
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$functionCall,
			$this->isNegated(),
			$this->moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function enterVariableAssign(string $variableName): self
	{
		$currentlyAssignedVariables = $this->currentlyAssignedVariables;
		$currentlyAssignedVariables[] = $variableName;

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			$this->isInFirstLevelStatement(),
			$currentlyAssignedVariables
		);
	}

	public function isInVariableAssign(string $variableName): bool
	{
		return in_array($variableName, $this->currentlyAssignedVariables, true);
	}

	public function assignVariable(
		string $variableName,
		Type $type = null
	): self
	{
		$variableTypes = $this->getVariableTypes();
		$variableTypes[$variableName] = $type ?? $this->typeFactory->createMixedType();

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function unsetVariable(string $variableName): self
	{
		if (!$this->hasVariableType($variableName)) {
			return $this;
		}
		$variableTypes = $this->getVariableTypes();
		unset($variableTypes[$variableName]);

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function intersectVariables(Scope $otherScope, bool $combineVariables): self
	{
		$ourVariableTypes = $this->getVariableTypes();
		$theirVariableTypes = $otherScope->getVariableTypes();
		$intersectedVariableTypes = [];
		foreach ($ourVariableTypes as $name => $variableType) {
			if (!isset($theirVariableTypes[$name])) {
				continue;
			}

			if ($combineVariables) {
				$variableType = $variableType->combineWith($theirVariableTypes[$name]);
			}

			$intersectedVariableTypes[$name] = $variableType;
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$intersectedVariableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function addVariables(Scope $otherScope, bool $removeSpecified = false): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($otherScope->getVariableTypes() as $name => $variableType) {
			if ($this->hasVariableType($name)) {
				$ourVariableType = $this->getVariableType($name);
				$variableNode = new Variable($name);
				if ($otherScope->isSpecified($variableNode) && $removeSpecified) {
					$ourVariableType = TypeCombinator::remove($ourVariableType, $otherScope->getType($variableNode));
				}

				if (!$removeSpecified) {
					$variableType = $ourVariableType->combineWith($variableType);
				}
			}
			$variableTypes[$name] = $variableType;
		}

		$moreSpecificTypes = $this->moreSpecificTypes;
		foreach ($otherScope->moreSpecificTypes as $exprString => $type) {
			if (array_key_exists($exprString, $this->moreSpecificTypes)) {
				$type = $type->combineWith($this->moreSpecificTypes[$exprString]);
			}
			$moreSpecificTypes[$exprString] = $type;
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function specifyExpressionType(Expr $expr, Type $type): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);

		$scope = $this->addMoreSpecificTypes([
			$exprString => $type,
		]);

		if ($expr instanceof Variable && is_string($expr->name)) {
			$variableName = $expr->name;

			$variableTypes = $scope->getVariableTypes();
			$variableTypes[$variableName] = $type;

			return new self(
				$scope->broker,
				$scope->printer,
				$scope->typeSpecifier,
				$scope->getFile(),
				$scope->getAnalysedContextFile(),
				$scope->isDeclareStrictTypes(),
				$scope->isInClass() ? $scope->getClassReflection() : null,
				$scope->getFunction(),
				$scope->getNamespace(),
				$variableTypes,
				$scope->inClosureBindScopeClass,
				$scope->getAnonymousFunctionReturnType(),
				$scope->getInFunctionCall(),
				$scope->isNegated(),
				$scope->moreSpecificTypes,
				$scope->inFirstLevelStatement
			);
		}

		return $scope;
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
				$this->getFile(),
				$this->getAnalysedContextFile(),
				$this->isDeclareStrictTypes(),
				$this->isInClass() ? $this->getClassReflection() : null,
				$this->getFunction(),
				$this->getNamespace(),
				$this->getVariableTypes(),
				$this->inClosureBindScopeClass,
				$this->getAnonymousFunctionReturnType(),
				$this->getInFunctionCall(),
				$this->isNegated(),
				$moreSpecificTypes,
				$this->inFirstLevelStatement
			);
		}

		return $this;
	}

	public function removeTypeFromExpression(Expr $expr, Type $type): self
	{
		return $this->specifyExpressionType(
			$expr,
			$this->typeFactory->createIntersectionType(
				$this->typeFactory->createFromLegacy($this->getType($expr)),
				$this->typeFactory->createComplementType($this->typeFactory->createFromLegacy($type))
			)
		);
	}

	public function lookForTypeSpecifications(Node $node): self
	{
		$scope = $this;
		$types = $this->typeSpecifier->specifyTypesInCondition(new SpecifiedTypes(), $scope, $node);
		foreach ($types->getSureTypes() as $type) {
			$scope = $scope->specifyExpressionType($type[0], $type[1]);
		}
		foreach ($types->getSureNotTypes() as $type) {
			$scope = $scope->removeTypeFromExpression($type[0], $type[1]);
		}

		return $scope;
	}

	public function lookForTypeSpecificationsInEarlyTermination(Node $node): self
	{
		$scope = $this;
		$types = $this->typeSpecifier->specifyTypesInCondition(new SpecifiedTypes(), $scope, $node);
		foreach ($types->getSureNotTypes() as $type) {
			$scope = $scope->specifyExpressionType($type[0], $type[1]);
		}
		foreach ($types->getSureTypes() as $type) {
			$scope = $scope->removeTypeFromExpression($type[0], $type[1]);
		}

		return $scope;
	}

	public function specifyFetchedPropertyFromIsset(PropertyFetch $expr): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);

		return $this->addMoreSpecificTypes([
			$exprString => $this->typeFactory->createMixedType(),
		]);
	}

	public function specifyFetchedStaticPropertyFromIsset(Expr\StaticPropertyFetch $expr): self
	{
		$exprString = $this->printer->prettyPrintExpr($expr);

		return $this->addMoreSpecificTypes([
			$exprString => $this->typeFactory->createMixedType(),
		]);
	}

	public function enterNegation(): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			!$this->isNegated(),
			$this->moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function enterFirstLevelStatements(): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			true,
			$this->currentlyAssignedVariables
		);
	}

	public function exitFirstLevelStatements(): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			false,
			$this->currentlyAssignedVariables
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function addSpecificTypesFromScope(self $otherScope): self
	{
		$moreSpecificTypes = $this->moreSpecificTypes;
		foreach ($otherScope->moreSpecificTypes as $exprString => $type) {
			$moreSpecificTypes[$exprString] = $type;
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->typeSpecifier,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$moreSpecificTypes,
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

}
