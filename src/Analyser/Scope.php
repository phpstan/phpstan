<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
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
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class Scope
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var string|null
	 */
	private $class;

	/**
	 * @var string|null
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
	 * @var bool
	 */
	private $inClosureBind;

	/**
	 * @var bool
	 */
	private $inAnonymousClass;

	/**
	 * @var string|null
	 */
	private $inFunctionCallName;

	public function __construct(
		Broker $broker,
		string $file,
		string $class = null,
		string $function = null,
		string $namespace = null,
		array $variablesTypes = [],
		bool $inClosureBind = false,
		bool $inAnonymousClass = false,
		string $inFunctionCallName = null
	)
	{
		if ($class === '') {
			$class = null;
		}
		if ($function === '') {
			$function = null;
		}
		if ($namespace === '') {
			$namespace = null;
		}
		$this->broker = $broker;
		$this->file = $file;
		$this->class = $class;
		$this->function = $function;
		$this->namespace = $namespace;
		$this->variableTypes = $variablesTypes;
		$this->inClosureBind = $inClosureBind;
		$this->inAnonymousClass = $inAnonymousClass;
		$this->inFunctionCallName = $inFunctionCallName;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	/**
	 * @return null|string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @return null|string
	 */
	public function getFunction()
	{
		return $this->function;
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

	public function isInClosureBind(): bool
	{
		return $this->inClosureBind;
	}

	public function isInAnonymousClass(): bool
	{
		return $this->inAnonymousClass;
	}

	/**
	 * @return string|null
	 */
	public function getInFunctionCallName()
	{
		return $this->inFunctionCallName;
	}

	public function getType(Node $node): Type
	{
		if ($node instanceof Variable && is_string($node->name)) {
			return $this->getVariableType($node->name);
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

				return $methodClassReflection->getMethod($node->name)->getReturnType();
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

				return $propertyClassReflection->getProperty($node->name)->getType();
			}
		}
		if ($node instanceof FuncCall && $node->name instanceof Name) {
			$functionName = (string) $node->name;
			if (!$this->broker->hasFunction($functionName)) {
				return new MixedType(true);
			}

			return $this->broker->getFunction($functionName)->getReturnType();
		}

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

		if (
			$node instanceof Node\Expr\BinaryOp\Div
			|| $node instanceof Node\Expr\AssignOp\Div
		) {
			return new FloatType(false);
		}

		if ($node instanceof Node\Expr\BinaryOp\Mod) {
			return new IntegerType(false);
		}

		if (
			$node instanceof Node\Expr\BinaryOp\Plus
			|| $node instanceof Node\Expr\BinaryOp\Minus
			|| $node instanceof Node\Expr\BinaryOp\Mul
			|| $node instanceof Node\Expr\BinaryOp\Pow
			|| $node instanceof Node\Expr\AssignOp
		)
		{
			if ($node instanceof Node\Expr\AssignOp) {
				$left = $node->var;
				$right = $node->expr;
			} else {
				$left = $node->left;
				$right = $node->right;
			}

			$leftType = $this->getType($left);
			$rightType = $this->getType($right);

			if ($leftType instanceof BooleanType) {
				$leftType = new IntegerType($leftType->isNullable());
			}
			if ($rightType instanceof BooleanType) {
				$rightType = new IntegerType($rightType->isNullable());
			}

			if ($leftType instanceof FloatType || $rightType instanceof FloatType) {
				return new FloatType(false);
			}

			if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
				return new IntegerType(false);
			}
		}

		switch (get_class($node)) {
			case LNumber::class:
				return new IntegerType(false);
			case ConstFetch::class:
				$constName = (string) $node->name;
				if (in_array($constName, ['true', 'false'], true)) {
					return new BooleanType(false);
				}
				if ($constName === 'null') {
					return new NullType();
				}
				break;
			case String_::class:
				return new StringType(false);
			case DNumber::class:
				return new FloatType(false);
			case New_::class:
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
				break;
			case Array_::class:
				return new ArrayType(false);
			case Int_::class:
				return new IntegerType(false);
			case Bool_::class:
				return new BooleanType(false);
			case Double::class:
				return new FloatType(false);
			case \PhpParser\Node\Expr\Cast\String_::class:
				return new StringType(false);
			case \PhpParser\Node\Expr\Cast\Array_::class:
				return new ArrayType(false);
			case Object_::class:
				return new ObjectType('stdClass', false);
			case Unset_::class:
				return new NullType();
		}

		// todo throw?
		return new MixedType(false);
	}

	public function enterClass(string $className): self
	{
		return new self(
			$this->broker,
			$this->getFile(),
			$className,
			null,
			$this->getNamespace(),
			[
				'this' => new ObjectType($className, false),
			]
		);
	}

	/**
	 * @param \PHPStan\Reflection\ParametersAcceptor $functionReflection
	 * @return self
	 */
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
			$this->getFile(),
			$this->getClass(),
			$functionReflection->getName(),
			$this->getNamespace(),
			$variableTypes
		);
	}

	/**
	 * @param \PHPStan\Analyser\string $methodName
	 * @param \PhpParser\Node\Param[] $parameters
	 */
	public function enterAnonymousClassMethod(
		string $methodName,
		array $parameters
	)
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($parameters as $parameter) {
			$isNullable = $parameter->default !== null
				&& $parameter->default instanceof ConstFetch
				&& (string) $parameter->default->name === 'null';

			$variableTypes[$parameter->name] = TypehintHelper::getTypeObjectFromTypehint((string) $parameter->type, $isNullable);
		}

		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$methodName,
			$this->getNamespace(),
			$variableTypes
		);
	}

	public function enterNamespace(string $namespaceName): self
	{
		return new self(
			$this->broker,
			$this->getFile(),
			null,
			null,
			$namespaceName
		);
	}

	public function enterClosureBind(): self
	{
		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			true,
			$this->isInAnonymousClass(),
			$this->getInFunctionCallName()
		);
	}

	public function enterAnonymousClass(): self
	{
		return new self(
			$this->broker,
			$this->getFile(),
			null,
			null,
			$this->getNamespace(),
			[
				'this' => new MixedType(false),
			],
			$this->isInClosureBind(),
			true,
			$this->getInFunctionCallName()
		);
	}

	/**
	 * @param \PhpParser\Node\Param[] $parameters
	 * @param \PhpParser\Node\Expr\ClosureUse[] $uses
	 * @return self
	 */
	public function enterAnonymousFunction(array $parameters, array $uses): self
	{
		$variableTypes = [];
		foreach ($parameters as $parameter) {
			// todo stejná logika ohledně zjištění typů jako v enterFunction
			$variableTypes[$parameter->name] = new MixedType(true);
		}
		foreach ($uses as $use) {
			// todo převzít typy z outer scope
			$variableTypes[$use->var] = new MixedType(true);
		}

		if ($this->getClass() !== null) {
			$variableTypes['this'] = new ObjectType($this->getClass(), false);
		}

		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->isInClosureBind(),
			$this->isInAnonymousClass(),
			$this->getInFunctionCallName()
		);
	}

	public function enterForeach(string $valueName, string $keyName = null): self
	{
		$variableTypes = $this->getVariableTypes();
		$variableTypes[$valueName] = new MixedType(true);
		if ($keyName !== null) {
			$variableTypes[$keyName] = new MixedType(false);
		}

		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->isInClosureBind(),
			$this->isInAnonymousClass()
		);
	}

	public function enterCatch(string $exceptionClassName, string $variableName): self
	{
		$variableTypes = $this->getVariableTypes();
		$variableTypes[$variableName] = new ObjectType($exceptionClassName, false);

		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->isInClosureBind(),
			$this->isInAnonymousClass()
		);
	}

	public function enterFunctionCall(string $functionName): self
	{
		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$this->getVariableTypes(),
			$this->isInClosureBind(),
			$this->isInAnonymousClass(),
			$functionName
		);
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
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->isInClosureBind(),
			$this->isInAnonymousClass(),
			$this->getInFunctionCallName()
		);
	}

	public function unsetVariable(string $variableName)
	{
		$this->getVariableType($variableName); // check if exists
		$variableTypes = $this->getVariableTypes();
		unset($variableTypes[$variableName]);

		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->isInClosureBind(),
			$this->isInAnonymousClass(),
			$this->getInFunctionCallName()
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
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$intersectedVariableTypes,
			$this->isInClosureBind(),
			$this->isInAnonymousClass(),
			$this->getInFunctionCallName()
		);
	}

	public function addVariables(Scope $otherScope): self
	{
		$variableTypes = $this->getVariableTypes();
		foreach ($otherScope->getVariableTypes() as $name => $variableType) {
			$variableTypes[$name] = $variableType;
		}

		return new self(
			$this->broker,
			$this->getFile(),
			$this->getClass(),
			$this->getFunction(),
			$this->getNamespace(),
			$variableTypes,
			$this->isInClosureBind(),
			$this->isInAnonymousClass(),
			$this->getInFunctionCallName()
		);
	}

}
