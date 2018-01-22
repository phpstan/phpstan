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
use PHPStan\TrinaryLogic;
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
use PHPStan\Type\ObjectWithoutClassType;
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
	 * @var \PHPStan\Analyser\VariableTypeHolder[]
	 */
	private $variableTypes;

	/**
	 * @var \PHPStan\Type\Type[]
	 */
	private $moreSpecificTypes;

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
		string $file,
		string $analysedContextFile = null,
		bool $declareStrictTypes = false,
		ClassReflection $classReflection = null,
		\PHPStan\Reflection\ParametersAcceptor $function = null,
		string $namespace = null,
		array $variablesTypes = [],
		array $moreSpecificTypes = [],
		string $inClosureBindScopeClass = null,
		Type $inAnonymousFunctionReturnType = null,
		Expr $inFunctionCall = null,
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
		$this->file = $file;
		$this->analysedContextFile = $analysedContextFile !== null ? $analysedContextFile : $file;
		$this->declareStrictTypes = $declareStrictTypes;
		$this->classReflection = $classReflection;
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
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalAnd
			|| $node instanceof \PhpParser\Node\Expr\BinaryOp\LogicalOr
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
			$conditionScope = $this->filterByTruthyValue($node->cond);
			$negatedConditionScope = $this->filterByFalseyValue($node->cond);
			$elseType = $negatedConditionScope->getType($node->else);
			if ($node->if === null) {
				return TypeCombinator::union($conditionScope->getType($node->cond), $elseType);
			}

			return TypeCombinator::union($conditionScope->getType($node->if), $elseType);
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
					TypeCombinator::union($leftType->getIterableKeyType(), $rightType->getIterableKeyType()),
					TypeCombinator::union($leftType->getItemType(), $rightType->getItemType()),
					$leftType->isItemTypeInferredFromLiteralArray() || $rightType->isItemTypeInferredFromLiteralArray()
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
			$itemTypes = array_map(
				function (Expr\ArrayItem $item): Type {
					return $this->getType($item->value);
				},
				$node->items
			);

			$callable = TrinaryLogic::createNo();
			if (count($itemTypes) === 2) {
				if (
					($itemTypes[0]->accepts(new StringType()) || count($itemTypes[0]->getReferencedClasses()) > 0)
					&& $itemTypes[1]->accepts(new StringType())
				) {
					$callable = TrinaryLogic::createYes();
				}
			}

			$arrayWithKeys = [];
			$keyExpressionTypes = [];
			foreach ($node->items as $arrayItem) {
				$itemKey = $arrayItem->key;
				if ($itemKey === null) {
					$arrayWithKeys[] = 'foo';
					continue;
				}

				if (
					$itemKey instanceof \PhpParser\Node\Scalar\String_
					|| $itemKey instanceof \PhpParser\Node\Scalar\LNumber
					|| $itemKey instanceof \PhpParser\Node\Scalar\DNumber
					|| $itemKey instanceof \PhpParser\Node\Expr\ConstFetch
				) {
					if ($itemKey instanceof \PhpParser\Node\Expr\ConstFetch) {
						$constName = strtolower((string) $itemKey->name);
						if ($constName === 'true') {
							$value = true;
						} elseif ($constName === 'false') {
							$value = false;
						} elseif ($constName === 'null') {
							$value = null;
						} elseif ($this->broker->hasConstant($itemKey->name, $this)) {
							$value = constant($this->broker->resolveConstantName($itemKey->name, $this));
						} else {
							$keyExpressionTypes[] = new MixedType();
							continue;
						}

						$arrayWithKeys[$value] = 'foo';
					} else {
						$arrayWithKeys[$itemKey->value] = 'foo';
					}
				} else {
					$keyExpressionTypes[] = $this->getType($itemKey);
				}
			}

			$scalarKeysTypes = array_map(function ($value): Type {
				return $this->getTypeFromValue($value);
			}, array_keys($arrayWithKeys));

			return new ArrayType(
				$this->getCombinedType(array_merge($scalarKeysTypes, $keyExpressionTypes)),
				$this->getCombinedType($itemTypes),
				true,
				$callable
			);
		} elseif ($node instanceof Int_) {
				return new IntegerType();
		} elseif ($node instanceof Bool_) {
			return new TrueOrFalseBooleanType();
		} elseif ($node instanceof Double) {
			return new FloatType();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
			return new StringType();
		} elseif ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
			return new ArrayType(new MixedType(), new MixedType());
		} elseif ($node instanceof Node\Scalar\MagicConst\Line) {
			return new IntegerType();
		} elseif ($node instanceof Node\Scalar\MagicConst) {
			return new StringType();
		} elseif ($node instanceof Object_) {
			return new ObjectType('stdClass');
		} elseif ($node instanceof Unset_) {
			return new NullType();
		} elseif ($node instanceof Node\Expr\ClassConstFetch && is_string($node->name)) {
			if ($node->class instanceof Name) {
				$constantClass = (string) $node->class;
				$constantClassType = new ObjectType($constantClass);
				if (in_array(strtolower($constantClass), [
					'self',
					'parent',
				], true)) {
					$resolvedName = $this->resolveName($node->class);
					$constantClassType = new ObjectType($resolvedName);
				}
			} else {
				$constantClassType = $this->getType($node->class);
			}

			$constantName = $node->name;
			if (strtolower($constantName) === 'class') {
				return new StringType();
			}
			if ($constantClassType->hasConstant($constantName)) {
				$constant = $constantClassType->getConstant($constantName);
				$typeFromValue = $this->getTypeFromValue($constant->getValue());
				if ($typeFromValue !== null) {
					return $typeFromValue;
				}
			}
		}

		$exprString = $this->printer->prettyPrintExpr($node);
		if (isset($this->moreSpecificTypes[$exprString])) {
			return $this->moreSpecificTypes[$exprString];
		}

		if ($node instanceof Variable && is_string($node->name)) {
			if ($this->hasVariableType($node->name)->no()) {
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

		if ($node instanceof FuncCall && $node->name instanceof Name) {
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

			return $functionReflection->getReturnType();
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
			return new ArrayType(
				$this->getCombinedType(
					array_map(function ($value): Type {
						return $this->getTypeFromValue($value);
					}, array_keys($value))
				),
				$this->getCombinedType(
					array_map(function ($value): Type {
						return $this->getTypeFromValue($value);
					}, array_values($value))
				),
				false
			);
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

		return TypeCombinator::union(...$types);
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
				'this' => VariableTypeHolder::createYes(new ThisType($classReflection->getName())),
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
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated()
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
			$variableTypes[$parameter->getName()] = VariableTypeHolder::createYes($parameter->getType());
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			if (!$this->hasVariableType($use->var)->yes()) {
				if ($use->byRef) {
					$variableTypes[$use->var] = VariableTypeHolder::createYes(new MixedType());
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			return new TrueOrFalseBooleanType();
		} elseif ($type === 'float') {
			return new FloatType();
		} elseif ($type === 'callable') {
			return new CallableType();
		} elseif ($type === 'array') {
			return new ArrayType(new MixedType(), new MixedType());
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
			return new IterableIterableType(new MixedType(), new MixedType());
		} elseif ($type === 'void') {
			return new VoidType();
		} elseif ($type === 'object') {
			return new ObjectWithoutClassType();
		} elseif ($type instanceof Node\NullableType) {
			return $this->getFunctionType($type->type, true, $isVariadic);
		}

		return new MixedType();
	}

	public function enterForeach(Expr $iteratee, string $valueName, string $keyName = null): self
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			$moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
	}

	public function unsetVariable(string $variableName): self
	{
		if ($this->hasVariableType($variableName)->no()) {
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
			$this->moreSpecificTypes,
			$this->inClosureBindScopeClass,
			$this->getAnonymousFunctionReturnType(),
			$this->getInFunctionCall(),
			$this->isNegated(),
			$this->inFirstLevelStatement
		);
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			if (preg_match('#^\$([a-zA-Z_][a-zA-Z0-9_]*)$#', $exprString, $matches) === 1) {
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
					&& $theirVariableTypeHolder->getType()->describe() === $ourVariableTypeHolders[$name]->getType()->describe()
					&& $ourVariableTypeHolders[$name]->getCertainty()->equals($theirVariableTypeHolder->getCertainty())
				) {
					unset($ourVariableTypeHolders[$name]);
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

		$scope = $this->addMoreSpecificTypes([
			$exprString => $type,
		]);

		if ($expr instanceof Variable && is_string($expr->name)) {
			$variableName = $expr->name;

			$variableTypes = $scope->getVariableTypes();
			$variableTypes[$variableName] = VariableTypeHolder::createYes($type);

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
				$scope->moreSpecificTypes,
				$scope->inClosureBindScopeClass,
				$scope->getAnonymousFunctionReturnType(),
				$scope->getInFunctionCall(),
				$scope->isNegated(),
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

	public function filterByTruthyValue(Expr $expr): self
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, TypeSpecifier::CONTEXT_TRUTHY);
		return $this->filterBySpecifiedTypes($specifiedTypes);
	}

	public function filterByFalseyValue(Expr $expr): self
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this, $expr, TypeSpecifier::CONTEXT_FALSEY);
		return $this->filterBySpecifiedTypes($specifiedTypes);
	}

	private function filterBySpecifiedTypes(SpecifiedTypes $specifiedTypes): self
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			$this->getFile(),
			$this->getAnalysedContextFile(),
			$this->isDeclareStrictTypes(),
			$this->isInClass() ? $this->getClassReflection() : null,
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
			$descriptions[$key] = $variableTypeHolder->getType()->describe();
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
