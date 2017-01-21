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
	 * @var string
	 */
	private $file;

	/**
	 * @var string
	 */
	private $analysedContextFile;

	/**
	 * @var bool
	 */
	private $declareStrictTypes;

	/**
	 * @var string|null
	 */
	private $class;

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
	 * @var string
	 */
	private $inClosureBindScopeClass;

	/**
	 * @var \PHPStan\Type\Type|null
	 */
	private $inAnonymousFunctionReturnType;

	/**
	 * @var \PHPStan\Reflection\ClassReflection
	 */
	private $anonymousClass;

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
	 * @param string $file
	 * @param string|null $analysedContextFile
	 * @param bool $declareStrictTypes
	 * @param string|null $class
	 * @param \PHPStan\Reflection\ParametersAcceptor|null $function
	 * @param string|null $namespace
	 * @param \PHPStan\Type\Type[] $variablesTypes
	 * @param string|null $inClosureBindScopeClass
	 * @param \PHPStan\Type\Type|null $inAnonymousFunctionReturnType
	 * @param \PHPStan\Reflection\ClassReflection|null $anonymousClass
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null $inFunctionCall
	 * @param bool $negated
	 * @param \PHPStan\Type\Type[] $moreSpecificTypes
	 * @param bool $inFirstLevelStatement
	 * @param string[] $currentlyAssignedVariables
	 */
	public function __construct(
		Broker $broker,
		\PhpParser\PrettyPrinter\Standard $printer,
		string $file,
		string $analysedContextFile = null,
		bool $declareStrictTypes = false,
		string $class = null,
		\PHPStan\Reflection\ParametersAcceptor $function = null,
		string $namespace = null,
		array $variablesTypes = [],
		string $inClosureBindScopeClass = null,
		Type $inAnonymousFunctionReturnType = null,
		ClassReflection $anonymousClass = null,
		Expr $inFunctionCall = null,
		bool $negated = false,
		array $moreSpecificTypes = [],
		bool $inFirstLevelStatement = true,
		array $currentlyAssignedVariables = []
	)
	{
		if ($class === '') {
			$class = null;
		}

		if ($namespace === '') {
			$namespace = null;
		}

		$this->broker = $broker;
		$this->printer = $printer;
		$this->file = $file;
		$this->analysedContextFile = $analysedContextFile !== null ? $analysedContextFile : $file;
		$this->declareStrictTypes = $declareStrictTypes;
		$this->class = $class;
		$this->function = $function;
		$this->namespace = $namespace;
		$this->variableTypes = $variablesTypes;
		$this->inClosureBindScopeClass = $inClosureBindScopeClass;
		$this->inAnonymousFunctionReturnType = $inAnonymousFunctionReturnType;
		$this->anonymousClass = $anonymousClass;
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			true
		);
	}

	/**
	 * @return null|string
	 */
	public function getClass()
	{
		return $this->class;
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

	public function isInAnonymousClass(): bool
	{
		return $this->anonymousClass !== null;
	}

	public function getAnonymousClass(): ClassReflection
	{
		return $this->anonymousClass;
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
		if (
			$node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr
			|| $node instanceof \PhpParser\Node\Expr\BooleanNot
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalXor
		) {
			return new TrueOrFalseBooleanType(false);
		}

		if (
			$node instanceof Node\Expr\UnaryMinus
			|| $node instanceof Node\Expr\UnaryPlus
		) {
			return $this->getType($node->expr);
		}

		if ($node instanceof Node\Expr\BinaryOp\Mod) {
			return new IntegerType(false);
		}

		if ($node instanceof Expr\BinaryOp\Concat) {
			return new StringType(false);
		}

		if ($node instanceof Expr\BinaryOp\Spaceship) {
			return new IntegerType(false);
		}

		if ($node instanceof Expr\Ternary) {
			$elseType = $this->getType($node->else);
			if ($node->if === null) {
				return $this->getType($node->cond)->combineWith($elseType);
			}
			return $this->getType($node->if)->combineWith($elseType);
		}

		if ($node instanceof Expr\BinaryOp\Coalesce) {
			return $this->getType($node->left)->combineWith($this->getType($node->right));
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

			if ($leftType instanceof BooleanType) {
				$leftType = new IntegerType($leftType->isNullable());
			}

			if ($rightType instanceof BooleanType) {
				$rightType = new IntegerType($rightType->isNullable());
			}

			if ($node instanceof Expr\AssignOp\Div || $node instanceof Expr\BinaryOp\Div) {
				if (!$leftType instanceof MixedType && !$rightType instanceof MixedType) {
					return new FloatType(false);
				}
			}

			if (
				($leftType instanceof FloatType && !$rightType instanceof MixedType)
				|| ($rightType instanceof FloatType && !$leftType instanceof MixedType)
			) {
				return new FloatType(false);
			}

			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				return new IntegerType(false);
			}
		}

		if ($node instanceof LNumber) {
			return new IntegerType(false);
		} elseif ($node instanceof ConstFetch) {
			$constName = strtolower((string) $node->name);
			if ($constName === 'true') {
				return new \PHPStan\Type\TrueBooleanType(false);
			} elseif ($constName === 'false') {
				return new \PHPStan\Type\FalseBooleanType(false);
			} elseif ($constName === 'null') {
				return new NullType();
			}
		} elseif ($node instanceof String_) {
			return new StringType(false);
		} elseif ($node instanceof DNumber) {
			return new FloatType(false);
		} elseif ($node instanceof Expr\Closure) {
			return new ObjectType('Closure', false);
		} elseif ($node instanceof New_) {
			if ($node->class instanceof Name) {
				if (
					count($node->class->parts) === 1
				) {
					if ($node->class->parts[0] === 'static') {
						return new StaticType($this->getClass(), false);
					} elseif ($node->class->parts[0] === 'self') {
						return new ObjectType($this->getClass(), false);
					}
				}

				return new ObjectType((string) $node->class, false);
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
			}, $node->items)), false, true, $possiblyCallable);
		} elseif ($node instanceof Int_) {
				return new IntegerType(false);
		} elseif ($node instanceof Bool_) {
			return new TrueOrFalseBooleanType(false);
		} elseif ($node instanceof Double) {
			return new FloatType(false);
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
			return new StringType(false);
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
			return new ArrayType(new MixedType(), false);
		} elseif ($node instanceof Object_) {
			return new ObjectType('stdClass', false);
		} elseif ($node instanceof Unset_) {
			return new NullType();
		} elseif ($node instanceof Node\Expr\ClassConstFetch) {
			if ($node->class instanceof Name) {
				$constantClass = (string) $node->class;
				if ($constantClass === 'self') {
					$constantClass = $this->getClass();
				}
			} elseif ($node->class instanceof Expr) {
				$constantClassType = $this->getType($node->class);
				if ($constantClassType->getClass() !== null) {
					$constantClass = $constantClassType->getClass();
				}
			}

			if (isset($constantClass)) {
				$constantName = $node->name;
				if (strtolower($constantName) === 'class') {
					return new StringType(false);
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
				return new MixedType();
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
					return new MixedType();
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

			if ($calleeClass !== null && $this->broker->hasClass($calleeClass)) {
				$staticMethodClassReflection = $this->broker->getClass($calleeClass);
				if (!$staticMethodClassReflection->hasMethod($node->name)) {
					return new MixedType();
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
					if ($nodeClassString === 'parent' && $this->getClass() !== null) {
						return $staticMethodReflection->getReturnType()->changeBaseClass($this->getClass());
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
					return new MixedType();
				}

				return $propertyClassReflection->getProperty($node->name, $this)->getType();
			}
		}

		if ($node instanceof Expr\StaticPropertyFetch && is_string($node->name) && $node->class instanceof Name) {
			$staticPropertyHolderClass = $this->resolveName($node->class);
			if ($staticPropertyHolderClass !== null && $this->broker->hasClass($staticPropertyHolderClass)) {
				$staticPropertyClassReflection = $this->broker->getClass(
					$staticPropertyHolderClass
				);
				if (!$staticPropertyClassReflection->hasProperty($node->name)) {
					return new MixedType();
				}

				return $staticPropertyClassReflection->getProperty($node->name, $this)->getType();
			}
		}

		if ($node instanceof FuncCall && $node->name instanceof Name) {
			$arrayFunctionsThatDependOnClosureReturnType = [
				'array_map' => 0,
				'array_reduce' => 1,
			];
			$functionName = (string) $node->name;
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
					false
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

				return new ArrayType($this->getType($argumentValue), false, true);
			}

			if (!$this->broker->hasFunction($node->name, $this)) {
				return new MixedType();
			}

			return $this->broker->getFunction($node->name, $this)->getReturnType();
		}

		return new MixedType();
	}

	/**
	 * @param \PhpParser\Node\Name $name
	 * @return string|null
	 */
	public function resolveName(Name $name)
	{
		$originalClass = (string) $name;
		if ($originalClass === 'self' || $originalClass === 'static') {
			return $this->getClass();
		} elseif ($originalClass === 'parent' && $this->getClass() !== null && $this->broker->hasClass($this->getClass())) {
			$currentClassReflection = $this->broker->getClass($this->getClass());
			if ($currentClassReflection->getParentClass() !== false) {
				return $currentClassReflection->getParentClass()->getName();
			}
		} else {
			return $originalClass;
		}

		return null;
	}

	/**
	 * @param mixed $value
	 * @return Type|null
	 */
	private function getTypeFromValue($value)
	{
		if (is_int($value)) {
			return new IntegerType(false);
		} elseif (is_float($value)) {
			return new FloatType(false);
		} elseif (is_bool($value)) {
			return new TrueOrFalseBooleanType(false);
		} elseif ($value === null) {
			return new NullType();
		} elseif (is_string($value)) {
			return new StringType(false);
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

		$itemType = reset($types);
		array_shift($types);
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

	public function enterClass(string $className): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$className,
			null,
			$this->getNamespace(),
			[
				'this' => new ThisType($className, false),
			]
		);
	}

	public function changeAnalysedContextFile(string $fileName): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$fileName,
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
				$this->getClass() !== null ? $this->broker->getClass($this->getClass()) : $this->getAnonymousClass(),
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$functionReflection,
			$this->getNamespace(),
			$variableTypes,
			null,
			null,
			$this->anonymousClass
		);
	}

	public function enterNamespace(string $namespaceName): self
	{
		return new self(
			$this->broker,
			$this->printer,
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

		if ($scopeClass === 'static') {
			$scopeClass = $this->getClass();
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$scopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			null,
			null,
			$this->getNamespace(),
			[
				'this' => new MixedType(),
			],
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$anonymousClass,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$returnType,
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
		if ($isVariadic) {
			return new ArrayType($this->getFunctionType(
				$type,
				$isNullable,
				false
			), false);
		}
		if ($type === null) {
			return new MixedType();
		} elseif ($type === 'string') {
			return new StringType($isNullable);
		} elseif ($type === 'int') {
			return new IntegerType($isNullable);
		} elseif ($type === 'bool') {
			return new TrueOrFalseBooleanType($isNullable);
		} elseif ($type === 'float') {
			return new FloatType($isNullable);
		} elseif ($type === 'callable') {
			return new CallableType($isNullable);
		} elseif ($type === 'array') {
			return new ArrayType(new MixedType(), $isNullable);
		} elseif ($type instanceof Name) {
			$className = (string) $type;
			if ($className === 'self') {
				$className = $this->getClass();
			} elseif (
				$className === 'parent'
			) {
				if (
					$this->getClass() !== null
					&& $this->broker->hasClass($this->getClass())
				) {
					$classReflection = $this->broker->getClass($this->getClass());
				} elseif ($this->isInAnonymousClass()) {
					$classReflection = $this->getAnonymousClass();
				} else {
					return new NonexistentParentClassType(false);
				}

				if ($classReflection->getParentClass() !== false) {
					return new ObjectType($classReflection->getParentClass()->getName(), $isNullable);
				}

				return new NonexistentParentClassType(false);
			}
			return new ObjectType($className, $isNullable);
		} elseif ($type === 'iterable') {
			return new IterableIterableType(new MixedType(), $isNullable);
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
		$variableTypes = $this->getVariableTypes();
		if ($iterateeType instanceof IterableType) {
			$variableTypes[$valueName] = $iterateeType->getItemType();
		} else {
			$variableTypes[$valueName] = new MixedType();
		}

		if ($keyName !== null) {
			$variableTypes[$keyName] = new MixedType();
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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

		if (count($classes) === 1) {
			$type = new ObjectType((string) $classes[0], false);
		} else {
			$type = new MixedType();
		}
		$variableTypes[$variableName] = $type;

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
		$variableTypes[$variableName] = $type !== null
			? $type
			: new MixedType();

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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

			$intersectedVariableTypes[$name] = $variableType->combineWith($theirVariableTypes[$name]);
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$intersectedVariableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function addVariables(Scope $otherScope): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($otherScope->getVariableTypes() as $name => $variableType) {
			if ($this->hasVariableType($name)) {
				$variableType = $this->getVariableType($name)->combineWith($variableType);
			}
			$variableTypes[$name] = $variableType;
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->moreSpecificTypes,
			$this->inFirstLevelStatement
		);
	}

	public function specifyExpressionType(Expr $expr, Type $type): self
	{
		if ($expr instanceof Variable && is_string($expr->name)) {
			$variableName = $expr->name;

			$variableTypes = $this->getVariableTypes();
			$variableTypes[$variableName] = $type;

			return new self(
				$this->broker,
				$this->printer,
				$this->getFile(),
				$this->getAnalysedContextFile(),
				$this->isDeclareStrictTypes(),
				$this->getClass(),
				$this->getFunction(),
				$this->getNamespace(),
				$variableTypes,
				$this->inClosureBindScopeClass,
				$this->getAnonymousFunctionReturnType(),
				$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
				$this->getInFunctionCall(),
				$this->isNegated(),
				$this->moreSpecificTypes,
				$this->inFirstLevelStatement
			);
		}

		$exprString = $this->printer->prettyPrintExpr($expr);

		return $this->addMoreSpecificTypes([
			$exprString => $type,
		]);
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->isInAnonymousClass() ? $this->getAnonymousClass() : null,
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

		$class = $this->inClosureBindScopeClass !== null ? $this->inClosureBindScopeClass : $this->getClass();
		if ($class !== null && $this->broker->hasClass($class)) {
			$currentClassReflection = $this->broker->getClass($class);
		} elseif ($this->isInAnonymousClass()) {
			$currentClassReflection = $this->getAnonymousClass();
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
