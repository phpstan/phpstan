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
use PHPStan\Type\ErrorType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableIterableType;
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

class Scope
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

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
	private $currentlyAssignedExpressions = [];

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
	 * @param string[] $currentlyAssignedExpressions
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
		array $currentlyAssignedExpressions = []
	)
	{
		if ($namespace === '') {
			$namespace = null;
		}

		$this->broker = $broker;
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
		$this->currentlyAssignedExpressions = $currentlyAssignedExpressions;
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
			|| $node instanceof Expr\BinaryOp\Identical
			|| $node instanceof Expr\BinaryOp\NotIdentical
			|| $node instanceof Expr\BinaryOp\Equal
			|| $node instanceof Expr\BinaryOp\NotEqual
			|| $node instanceof Expr\Instanceof_
			|| $node instanceof Expr\Isset_
			|| $node instanceof Expr\Empty_
		) {
			return new TrueOrFalseBooleanType();
		}

		if (
			$node instanceof Node\Expr\UnaryMinus
			|| $node instanceof Node\Expr\UnaryPlus
			|| $node instanceof Expr\ErrorSuppress
			|| $node instanceof Expr\Assign
		) {
			return $this->getType($node->expr);
		}

		if ($node instanceof Node\Expr\BinaryOp\Mod) {
			return new IntegerType();
		}

		if ($node instanceof Expr\BinaryOp\Concat) {
			return new StringType();
		}

		if ($node instanceof Expr\BinaryOp\Spaceship) {
			return new IntegerType();
		}

		if ($node instanceof Expr\Ternary) {
			$elseType = $this->getType($node->else);
			if ($node->if === null) {
				return TypeCombinator::removeNull(
					$this->getType($node->cond)
				)->combineWith($elseType);
			}

			$conditionScope = $this->filterByTruthyValue($node->cond);
			$ifType = $conditionScope->getType($node->if);
			$negatedConditionScope = $this->filterByFalseyValue($node->cond);
			$elseType = $negatedConditionScope->getType($node->else);

			return $ifType->combineWith($elseType);
		}

		if ($node instanceof Expr\BinaryOp\Coalesce) {
			return TypeCombinator::removeNull(
				$this->getType($node->left)
			)->combineWith($this->getType($node->right));
		}

		if ($node instanceof Expr\Clone_) {
			return $this->getType($node->expr);
		}

		if ($node instanceof Expr\AssignOp\Concat) {
			return new StringType();
		}

		if (
			$node instanceof Expr\AssignOp\ShiftLeft
			|| $node instanceof Expr\AssignOp\ShiftRight
			|| $node instanceof Expr\AssignOp\Mod
		) {
			return new IntegerType();
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

			if ($leftType instanceof BooleanType) {
				$leftType = new IntegerType();
			}

			if ($rightType instanceof BooleanType) {
				$rightType = new IntegerType();
			}

			if ($node instanceof Expr\AssignOp\Div || $node instanceof Expr\BinaryOp\Div) {
				if (!$leftType instanceof MixedType && !$rightType instanceof MixedType) {
					return new FloatType();
				}
			}

			if (
				($leftType instanceof FloatType && !$rightType instanceof MixedType)
				|| ($rightType instanceof FloatType && !$leftType instanceof MixedType)
			) {
				return new FloatType();
			}

			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				return new IntegerType();
			}

			if (
				($node instanceof Expr\AssignOp\Plus || $node instanceof Expr\BinaryOp\Plus)
				&& $leftType instanceof ArrayType
				&& $rightType instanceof ArrayType
			) {
				return new ArrayType(
					$leftType->getItemType()->combineWith($rightType->getItemType())
				);
			}
		}

		if ($node instanceof LNumber) {
			return new IntegerType();
		} elseif ($node instanceof ConstFetch) {
			$constName = strtolower((string) $node->name);
			if ($constName === 'true') {
				return new \PHPStan\Type\TrueBooleanType();
			} elseif ($constName === 'false') {
				return new \PHPStan\Type\FalseBooleanType();
			} elseif ($constName === 'null') {
				return new NullType();
			}

			if ($this->broker->hasConstant($node->name, $this)) {
				$typeFromValue = $this->getTypeFromValue(
					constant($this->broker->resolveConstantName($node->name, $this))
				);
				if ($typeFromValue !== null) {
					return $typeFromValue;
				}
			}
		} elseif ($node instanceof String_ || $node instanceof Node\Scalar\Encapsed) {
			return new StringType();
		} elseif ($node instanceof DNumber) {
			return new FloatType();
		} elseif ($node instanceof Expr\Closure) {
			return new ObjectType('Closure');
		} elseif ($node instanceof New_) {
			if ($node->class instanceof Name) {
				if (
					count($node->class->parts) === 1
				) {
					if ($node->class->parts[0] === 'static') {
						return new StaticType($this->getClassReflection()->getName());
					} elseif ($node->class->parts[0] === 'self') {
						return new ObjectType($this->getClassReflection()->getName());
					}
				}

				return new ObjectType((string) $node->class);
			}
		} elseif ($node instanceof Array_) {
			$possiblyCallable = false;
			if (count($node->items) === 2) {
				$firstItem = $node->items[0]->value;
				if (
					(
						$this->getType($firstItem)->getClass() !== null
						|| $this->getType($firstItem) instanceof StringType
					)
					&& $this->getType($node->items[1]->value) instanceof StringType
				) {
					$possiblyCallable = true;
				}
			}
			return new ArrayType($this->getCombinedType(array_map(function (Expr\ArrayItem $item): Type {
				return $this->getType($item->value);
			}, $node->items)), true, $possiblyCallable);
		} elseif ($node instanceof Int_) {
				return new IntegerType();
		} elseif ($node instanceof Bool_) {
			return new TrueOrFalseBooleanType();
		} elseif ($node instanceof Double) {
			return new FloatType();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
			return new StringType();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
			return new ArrayType(new MixedType());
		} elseif ($node instanceof Node\Scalar\MagicConst\Line) {
			return new IntegerType();
		} elseif ($node instanceof Node\Scalar\MagicConst) {
			return new StringType();
		} elseif ($node instanceof Object_) {
			return new ObjectType('stdClass');
		} elseif ($node instanceof Unset_) {
			return new NullType();
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
					return new StringType();
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

		$exprString = $this->printer->prettyPrintExpr($node);
		if (isset($this->moreSpecificTypes[$exprString])) {
			return $this->moreSpecificTypes[$exprString];
		}

		if ($node instanceof Variable && is_string($node->name)) {
			if (!$this->hasVariableType($node->name)) {
				return new ErrorType();
			}

			return $this->getVariableType($node->name);
		}

		if ($node instanceof Expr\ArrayDimFetch && $node->dim !== null) {
			$arrayType = $this->getType($node->var);
			if ($arrayType instanceof ArrayType) {
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
					return new ErrorType();
				}

				$methodReflection = $methodClassReflection->getMethod($node->name, $this);
				foreach ($this->broker->getDynamicMethodReturnTypeExtensionsForClass($methodClassReflection->getName()) as $dynamicMethodReturnTypeExtension) {
					if (!$dynamicMethodReturnTypeExtension->isMethodSupported($methodReflection)) {
						continue;
					}

					return $dynamicMethodReturnTypeExtension->getTypeFromMethodCall($methodReflection, $node, $this);
				}

				$calledOnThis = $node->var instanceof Variable && is_string($node->var->name) && $node->var->name === 'this';
				$methodReturnType = $methodReflection->getReturnType();
				if ($methodReturnType instanceof StaticResolvableType) {
					if ($calledOnThis) {
						if ($this->isInClass()) {
							return $methodReturnType->changeBaseClass($this->getClassReflection()->getName());
						}
					} else {
						return $methodReturnType->resolveStatic($methodClassReflection->getName());
					}
				}

				return $methodReflection->getReturnType();
			}
		}

		if ($node instanceof Expr\StaticCall && is_string($node->name)) {
			if ($node->class instanceof Name) {
				$calleeClass = $this->resolveName($node->class);
			} else {
				$calleeClass = $this->getType($node->class)->getClass();
			}

			if ($calleeClass !== null && $this->broker->hasClass($calleeClass)) {
				$staticMethodClassReflection = $this->broker->getClass($calleeClass);
				if (!$staticMethodClassReflection->hasMethod($node->name)) {
					return new ErrorType();
				}
				$staticMethodReflection = $staticMethodClassReflection->getMethod($node->name, $this);
				foreach ($this->broker->getDynamicStaticMethodReturnTypeExtensionsForClass($staticMethodClassReflection->getName()) as $dynamicStaticMethodReturnTypeExtension) {
					if (!$dynamicStaticMethodReturnTypeExtension->isStaticMethodSupported($staticMethodReflection)) {
						continue;
					}

					return $dynamicStaticMethodReturnTypeExtension->getTypeFromStaticMethodCall($staticMethodReflection, $node, $this);
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

					return $staticMethodReflection->getReturnType()->resolveStatic($staticMethodClassReflection->getName());
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
					return new ErrorType();
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
					return new ErrorType();
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

				return new ArrayType(
					$anonymousFunctionType,
					true
				);
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

				return new ArrayType($this->getType($argumentValue), true, true);
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
					if ($argumentType instanceof ArrayType) {
						return $argumentType->getItemType();
					}
				}

				if (count($node->args) === 1) {
					$argumentType = $this->getType($node->args[0]->value);
					if ($argumentType instanceof ArrayType) {
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

				/** @var \PHPStan\Type\Type $argumentType */
				$argumentType = $argumentType;

				return $argumentType;
			}

			if (!$this->broker->hasFunction($node->name, $this)) {
				return new ErrorType();
			}

			return $this->broker->getFunction($node->name, $this)->getReturnType();
		}

		return new MixedType();
	}

	public function resolveName(Name $name): string
	{
		$originalClass = (string) $name;
		if (in_array(strtolower($originalClass), [
			'self',
			'static',
		], true)) {
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
	 * @return Type|null
	 */
	private function getTypeFromValue($value)
	{
		if (is_int($value)) {
			return new IntegerType();
		} elseif (is_float($value)) {
			return new FloatType();
		} elseif (is_bool($value)) {
			return new TrueOrFalseBooleanType();
		} elseif ($value === null) {
			return new NullType();
		} elseif (is_string($value)) {
			return new StringType();
		} elseif (is_array($value)) {
			return new ArrayType($this->getCombinedType(array_map(function ($value): Type {
				return $this->getTypeFromValue($value);
			}, $value)), false);
		}

		return null;
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @return \PHPStan\Type\Type
	 */
	private function getCombinedType(array $types): Type
	{
		if (count($types) === 0) {
			return new MixedType();
		}

		$itemType = null;
		foreach ($types as $type) {
			if ($itemType === null) {
				$itemType = $type;
				continue;
			}
			$itemType = $itemType->combineWith($type);
		}

		return $itemType;
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
				'this' => new ThisType($classReflection->getName()),
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
				'this' => new MixedType(true),
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
					$variableTypes[$use->var] = new MixedType();
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
			return TypeCombinator::addNull(
				$this->getFunctionType($type, false, $isVariadic)
			);
		}
		if ($isVariadic) {
			return new ArrayType($this->getFunctionType(
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
			return new TrueOrFalseBooleanType();
		} elseif ($type === 'float') {
			return new FloatType();
		} elseif ($type === 'callable') {
			return new CallableType();
		} elseif ($type === 'array') {
			return new ArrayType(new MixedType());
		} elseif ($type instanceof Name) {
			$className = (string) $type;
			if ($className === 'self') {
				$className = $this->getClassReflection()->getName();
			} elseif (
				$className === 'parent'
			) {
				if ($this->isInClass() && $this->getClassReflection()->getParentClass() !== false) {
					return new ObjectType($this->getClassReflection()->getParentClass()->getName());
				}

				return new NonexistentParentClassType();
			}
			return new ObjectType($className);
		} elseif ($type === 'iterable') {
			return new IterableIterableType(new MixedType());
		} elseif ($type === 'void') {
			return new VoidType();
		} elseif ($type instanceof Node\NullableType) {
			return $this->getFunctionType($type->type, true, $isVariadic);
		}

		return new MixedType();
	}

	public function enterForeach(Expr $iteratee, string $valueName, string $keyName = null): self
	{
		$iterateeType = $this->getType($iteratee);
		$scope = $this->assignVariable($valueName, $iterateeType->getIterableValueType());

		if ($keyName !== null) {
			$scope = $scope->assignVariable($keyName, $iterateeType->getIterableKeyType());
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
		if (count($classes) === 1) {
			$type = new ObjectType((string) $classes[0]);
		} else {
			$type = new MixedType();
		}

		return $this->assignVariable($variableName, $type);
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

	public function enterExpressionAssign(Expr $expr): self
	{
		$currentlyAssignedExpressions = $this->currentlyAssignedExpressions;
		$currentlyAssignedExpressions[] = $this->printer->prettyPrintExpr($expr);

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
		Type $type = null
	): self
	{
		$variableTypes = $this->getVariableTypes();
		$variableTypes[$variableName] = $type !== null
			? $type
			: new MixedType();

		$exprString = $this->printer->prettyPrintExpr(new Variable($variableName));
		$moreSpecificTypes = $this->moreSpecificTypes;
		if (array_key_exists($exprString, $moreSpecificTypes)) {
			unset($moreSpecificTypes[$exprString]);
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

	public function intersectVariables(Scope $otherScope): self
	{
		$ourVariableTypes = $this->getVariableTypes();
		$theirVariableTypes = $otherScope->getVariableTypes();
		$intersectedVariableTypes = [];
		foreach ($ourVariableTypes as $name => $variableType) {
			if (!isset($theirVariableTypes[$name])) {
				continue;
			}

			if (!$this->isSpecified(new Variable($name))) {
				$variableType = $variableType->combineWith($theirVariableTypes[$name]);
			}

			$intersectedVariableTypes[$name] = $variableType;
		}

		$theirSpecifiedTypes = $otherScope->moreSpecificTypes;
		$intersectedSpecifiedTypes = [];
		foreach ($this->moreSpecificTypes as $exprString => $specificType) {
			if (!isset($theirSpecifiedTypes[$exprString])) {
				continue;
			}

			$intersectedSpecifiedTypes[$exprString] = $specificType->combineWith($theirSpecifiedTypes[$exprString]);
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
			$intersectedSpecifiedTypes,
			$this->inFirstLevelStatement
		);
	}

	public function createIntersectedScope(self $otherScope): self
	{
		$variableTypes = [];
		foreach ($otherScope->getVariableTypes() as $name => $variableType) {
			$variableTypes[$name] = $variableType;
		}

		$specifiedTypes = [];
		foreach ($otherScope->moreSpecificTypes as $exprString => $specificType) {
			$specifiedTypes[$exprString] = $specificType;
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
			$specifiedTypes,
			$this->inFirstLevelStatement
		);
	}

	public function mergeWithIntersectedScope(self $intersectedScope): self
	{
		$variableTypes = $this->variableTypes;
		$specifiedTypes = $this->moreSpecificTypes;
		foreach ($intersectedScope->getVariableTypes() as $name => $variableType) {
			$variableTypes[$name] = $variableType;

			$exprString = $this->printer->prettyPrintExpr(new Variable($name));
			unset($specifiedTypes[$exprString]);
		}

		foreach ($intersectedScope->moreSpecificTypes as $exprString => $specificType) {
			if (preg_match('#^\$([a-zA-Z_][a-zA-Z0-9_]*)$#', $exprString, $matches) === 1) {
				$variableName = $matches[1];
				$variableTypes[$variableName] = $specificType;
				continue;
			}
			$specifiedTypes[$exprString] = $specificType;
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
			$specifiedTypes,
			$this->inFirstLevelStatement
		);
	}

	public function addVariables(Scope $otherScope): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($otherScope->getVariableTypes() as $name => $theirVariableType) {
			if ($this->hasVariableType($name)) {
				$ourVariableType = $this->getVariableType($name);
				$theirVariableType = $ourVariableType->combineWith($theirVariableType);
			}
			$variableTypes[$name] = $theirVariableType;
		}

		$moreSpecificTypes = $this->moreSpecificTypes;
		foreach ($otherScope->moreSpecificTypes as $exprString => $theirSpecifiedType) {
			if (array_key_exists($exprString, $this->moreSpecificTypes)) {
				$ourSpecifiedType = $this->moreSpecificTypes[$exprString];
				$theirSpecifiedType = $ourSpecifiedType->combineWith($theirSpecifiedType);
			}
			$moreSpecificTypes[$exprString] = $theirSpecifiedType;
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

	public function removeVariables(self $otherScope, bool $all): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($otherScope->getVariableTypes() as $name => $variableType) {
			if ($all) {
				unset($variableTypes[$name]);
			} else {
				if (
					isset($variableTypes[$name])
					&& (
						$variableType->describe() === $variableTypes[$name]->describe()
						|| $this->isSpecified(new Variable($name))
					)
				) {
					unset($variableTypes[$name]);
				}
			}
		}

		$moreSpecificTypes = $this->moreSpecificTypes;
		foreach ($otherScope->moreSpecificTypes as $exprString => $specifiedType) {
			if (isset($moreSpecificTypes[$exprString]) && $specifiedType->describe() === $moreSpecificTypes[$exprString]->describe()) {
				unset($moreSpecificTypes[$exprString]);
			}
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
			TypeCombinator::remove($this->getType($expr), $type)
		);
	}

	public function filterByTruthyValue(Expr $expr): self
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, false);
		return $this->filterBySpecifiedTypes($specifiedTypes);
	}

	public function filterByFalseyValue(Expr $expr): self
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, true);
		return $this->filterBySpecifiedTypes($specifiedTypes);
	}

	private function filterBySpecifiedTypes(SpecifiedTypes $specifiedTypes): self
	{
		$scope = $this;
		foreach ($specifiedTypes->getSureTypes() as $type) {
			$scope = $scope->specifyExpressionType($type[0], $type[1]);
		}
		foreach ($specifiedTypes->getSureNotTypes() as $type) {
			$scope = $scope->removeTypeFromExpression($type[0], $type[1]);
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
			$this->currentlyAssignedExpressions
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
		foreach ($this->getVariableTypes() as $name => $variableType) {
			$descriptions[sprintf('$%s', $name)] = $variableType->describe();
		}
		foreach ($this->moreSpecificTypes as $exprString => $type) {
			$key = $exprString;
			if (isset($descriptions[$key])) {
				$key .= '-specified';
			}
			$descriptions[$key] = $type->describe();
		}

		return $descriptions;
	}

}
