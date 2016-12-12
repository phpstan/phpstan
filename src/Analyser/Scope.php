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
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableIterableType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
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
	 * @var string[]
	 */
	private $currentlyAssignedVariables = [];

	public function __construct(
		Broker $broker,
		\PhpParser\PrettyPrinter\Standard $printer,
		string $file,
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
		$this->currentlyAssignedVariables = $currentlyAssignedVariables;
	}

	public function getFile(): string
	{
		return $this->file;
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
	 * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|null
	 */
	public function getInFunctionCall()
	{
		return $this->inFunctionCall;
	}

	public function getType(Node $node): Type
	{
		if (
			$node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr
			|| $node instanceof \PhpParser\Node\Expr\BooleanNot
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalXor
		) {
			return new BooleanType(false);
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
			if (in_array($constName, ['true', 'false'], true)) {
				return new BooleanType(false);
			}

			if ($constName === 'null') {
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
						return new MixedType(false);
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
						$this->getType($firstItem) instanceof ObjectType
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
			return new BooleanType(false);
		} elseif ($node instanceof Double) {
			return new FloatType(false);
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
			return new StringType(false);
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
			return new ArrayType(new MixedType(true), false);
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
			} else {
				$constantClassType = $this->getType($node->class);
				if ($constantClassType->getClass() !== null) {
					$constantClass = $constantClassType->getClass();
				}
			}

			if (isset($constantClass)) {
				$constantName = $node->name;
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
				return new MixedType(true);
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
					return new MixedType(true);
				}

				$methodReflection = $methodClassReflection->getMethod($node->name);
				foreach ($this->broker->getDynamicMethodReturnTypeExtensionsForClass($methodCalledOnType->getClass()) as $dynamicMethodReturnTypeExtension) {
					if (!$dynamicMethodReturnTypeExtension->isMethodSupported($methodReflection)) {
						continue;
					}

					return $dynamicMethodReturnTypeExtension->getTypeFromMethodCall($methodReflection, $node, $this);
				}

				if ($methodReflection->getReturnType() instanceof StaticType) {
					if ($methodReflection->getReturnType()->isNullable()) {
						return $methodCalledOnType->makeNullable();
					}

					return $methodCalledOnType;
				}

				return $methodReflection->getReturnType();
			}
		}

		if ($node instanceof Expr\StaticCall && is_string($node->name) && $node->class instanceof Name) {
			$calleeClass = $this->resolveName($node->class);

			if ($calleeClass !== null && $this->broker->hasClass($calleeClass)) {
				$staticMethodClassReflection = $this->broker->getClass($calleeClass);
				if (!$staticMethodClassReflection->hasMethod($node->name)) {
					return new MixedType(true);
				}
				$staticMethodReflection = $staticMethodClassReflection->getMethod($node->name);
				if ($staticMethodReflection->getReturnType() instanceof StaticType) {
					$nodeClassString = (string) $node->class;
					if ($nodeClassString === 'parent' && $this->getClass() !== null) {
						return new StaticType($this->getClass(), $staticMethodReflection->getReturnType()->isNullable());
					}

					$calleeType = new ObjectType($calleeClass, false);
					if ($staticMethodReflection->getReturnType()->isNullable()) {
						return $calleeType->makeNullable();
					}

					return $calleeType;
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
					return new MixedType(true);
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
					return new MixedType(true);
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
				$anonymousFunctionType = $this->getAnonymousFunctionType($closure->returnType, $closure->returnType === null);
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

			if (!$this->broker->hasFunction($node->name, $this)) {
				return new MixedType(true);
			}

			return $this->broker->getFunction($node->name, $this)->getReturnType();
		}

		return new MixedType(false);
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
			return new BooleanType(false);
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
			return new MixedType(true);
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

	public function isSpecified(Node $node): bool
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
			$this->isDeclareStrictTypes(),
			$className,
			null,
			$this->getNamespace(),
			[
				'this' => new ObjectType($className, false),
			]
		);
	}

	public function enterFunction(
		ParametersAcceptor $functionReflection
	): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($functionReflection->getParameters() as $parameter) {
			$variableTypes[$parameter->getName()] = $parameter->getType();
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
			$this->isDeclareStrictTypes(),
			$this->getClass(),
			$functionReflection,
			$this->getNamespace(),
			$variableTypes
		);
	}

	public function enterNamespace(string $namespaceName): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
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
			$this->isDeclareStrictTypes(),
			null,
			null,
			$this->getNamespace(),
			[
				'this' => new MixedType(false),
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
	 * @param \PhpParser\Node\Name|string|null $returnTypehint
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
			$isNullable = false;
			if ($parameter->default instanceof ConstFetch && $parameter->default->name instanceof Name) {
				$isNullable = strtolower((string) $parameter->default->name) === 'null';
			}

			$variableTypes[$parameter->name] = $this->getAnonymousFunctionType($parameter->type, $isNullable);
		}

		foreach ($uses as $use) {
			if (!$this->hasVariableType($use->var)) {
				if ($use->byRef) {
					$variableTypes[$use->var] = new MixedType(true);
				}
				continue;
			}
			$variableTypes[$use->var] = $this->getVariableType($use->var);
		}

		if ($this->hasVariableType('this')) {
			$variableTypes['this'] = $this->getVariableType('this');
		}

		$returnType = $this->getAnonymousFunctionType($returnTypehint, $returnTypehint === null);

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
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

	/**
	 * @param \PhpParser\Node\Name|string|null $type
	 * @param bool $isNullable
	 * @return Type
	 */
	private function getAnonymousFunctionType($type = null, bool $isNullable): Type
	{
		if ($type === null) {
			return new MixedType(true);
		} elseif ($type === 'string') {
			return new StringType($isNullable);
		} elseif ($type === 'int') {
			return new IntegerType($isNullable);
		} elseif ($type === 'bool') {
			return new BooleanType($isNullable);
		} elseif ($type === 'float') {
			return new FloatType($isNullable);
		} elseif ($type === 'callable') {
			return new CallableType($isNullable);
		} elseif ($type === 'array') {
			return new ArrayType(new MixedType(true), $isNullable);
		} elseif ($type instanceof Name) {
			$className = (string) $type;
			if ($className === 'self') {
				$className = $this->getClass();
			}
			return new ObjectType($className, $isNullable);
		} elseif ($type === 'iterable') {
			return new IterableIterableType(new MixedType(true), $isNullable);
		} elseif ($type === 'void') {
			return new VoidType();
		} elseif ($type instanceof Node\NullableType) {
			return $this->getAnonymousFunctionType($type->type, true);
		}

		return new MixedType($isNullable);
	}

	public function enterForeach(Node $iteratee, string $valueName, string $keyName = null): self
	{
		$iterateeType = $this->getType($iteratee);
		$variableTypes = $this->getVariableTypes();
		if ($iterateeType instanceof IterableType) {
			$variableTypes[$valueName] = $iterateeType->getItemType();
		} else {
			$variableTypes[$valueName] = new MixedType(true);
		}

		if ($keyName !== null) {
			$variableTypes[$keyName] = new MixedType(false);
		}

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
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
			$type = new MixedType(false);
		}
		$variableTypes[$variableName] = $type;

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
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
			$this->moreSpecificTypes
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
			: new MixedType(true);

		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
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
			$this->moreSpecificTypes
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
			$this->moreSpecificTypes
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
			$this->moreSpecificTypes
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
			$this->moreSpecificTypes
		);
	}

	public function specifyExpressionType(Node $expr, Type $type): self
	{
		if ($expr instanceof Variable && is_string($expr->name)) {
			$variableName = $expr->name;

			$variableTypes = $this->getVariableTypes();
			$variableTypes[$variableName] = $type;

			return new self(
				$this->broker,
				$this->printer,
				$this->getFile(),
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
				$this->moreSpecificTypes
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
			$exprString => new MixedType(false),
		]);
	}

	public function enterNegation(): self
	{
		return new self(
			$this->broker,
			$this->printer,
			$this->getFile(),
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
			$this->moreSpecificTypes
		);
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
			$moreSpecificTypes
		);
	}

	public function canAccessProperty(PropertyReflection $propertyReflection): bool
	{
		return $this->canAccessClassMember($propertyReflection);
	}

	public function canCallMethod(MethodReflection $methodReflection): bool
	{
		return $this->canAccessClassMember($methodReflection);
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
		if ($class === null) {
			return false;
		}
		if (!$this->broker->hasClass($class)) {
			return false;
		}

		$classReflectionName = $classMemberReflection->getDeclaringClass()->getName();
		if ($classMemberReflection->isPrivate()) {
			return $class === $classReflectionName;
		}

		$currentClassReflection = $this->broker->getClass($class);

		// protected

		if (
			$currentClassReflection->getName() === $classReflectionName
			|| $currentClassReflection->isSubclassOf($classReflectionName)
		) {
			return true;
		}

		return $classMemberReflection->isStatic()
			&& $classMemberReflection->getDeclaringClass()->isSubclassOf($class);
	}

}
