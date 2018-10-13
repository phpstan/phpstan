<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Broker\Broker;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class TypeSpecifier
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Type\FunctionTypeSpecifyingExtension[] */
	private $functionTypeSpecifyingExtensions = [];

	/** @var \PHPStan\Type\MethodTypeSpecifyingExtension[] */
	private $methodTypeSpecifyingExtensions = [];

	/** @var \PHPStan\Type\StaticMethodTypeSpecifyingExtension[] */
	private $staticMethodTypeSpecifyingExtensions = [];

	/** @var \PHPStan\Type\MethodTypeSpecifyingExtension[][]|null */
	private $methodTypeSpecifyingExtensionsByClass;

	/** @var \PHPStan\Type\StaticMethodTypeSpecifyingExtension[][]|null */
	private $staticMethodTypeSpecifyingExtensionsByClass;

	/**
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Type\FunctionTypeSpecifyingExtension[] $functionTypeSpecifyingExtensions
	 * @param \PHPStan\Type\MethodTypeSpecifyingExtension[] $methodTypeSpecifyingExtensions
	 * @param \PHPStan\Type\StaticMethodTypeSpecifyingExtension[] $staticMethodTypeSpecifyingExtensions
	 */
	public function __construct(
		\PhpParser\PrettyPrinter\Standard $printer,
		Broker $broker,
		array $functionTypeSpecifyingExtensions,
		array $methodTypeSpecifyingExtensions,
		array $staticMethodTypeSpecifyingExtensions
	)
	{
		$this->printer = $printer;
		$this->broker = $broker;

		foreach (array_merge($functionTypeSpecifyingExtensions, $methodTypeSpecifyingExtensions, $staticMethodTypeSpecifyingExtensions) as $extension) {
			if (!($extension instanceof TypeSpecifierAwareExtension)) {
				continue;
			}

			$extension->setTypeSpecifier($this);
		}

		$this->functionTypeSpecifyingExtensions = $functionTypeSpecifyingExtensions;
		$this->methodTypeSpecifyingExtensions = $methodTypeSpecifyingExtensions;
		$this->staticMethodTypeSpecifyingExtensions = $staticMethodTypeSpecifyingExtensions;
	}

	public function specifyTypesInCondition(
		Scope $scope,
		Expr $expr,
		TypeSpecifierContext $context,
		bool $defaultHandleFunctions = false
	): SpecifiedTypes
	{
		if ($expr instanceof Instanceof_) {
			if ($expr->class instanceof Name) {
				$className = (string) $expr->class;
				$lowercasedClassName = strtolower($className);
				if ($lowercasedClassName === 'self' && $scope->isInClass()) {
					$type = new ObjectType($scope->getClassReflection()->getName());
				} elseif ($lowercasedClassName === 'static' && $scope->isInClass()) {
					$type = new StaticType($scope->getClassReflection()->getName());
				} elseif ($lowercasedClassName === 'parent') {
					if (
						$scope->isInClass()
						&& $scope->getClassReflection()->getParentClass() !== false
					) {
						$type = new ObjectType($scope->getClassReflection()->getParentClass()->getName());
					} else {
						$type = new NonexistentParentClassType();
					}
				} else {
					$type = new ObjectType($className);
				}
				return $this->create($expr->expr, $type, $context);
			}

			if ($context->true()) {
				return $this->create($expr->expr, new ObjectWithoutClassType(), $context);
			}
		} elseif ($expr instanceof Node\Expr\BinaryOp\Identical) {
			$expressions = $this->findTypeExpressionsFromBinaryOperation($scope, $expr);
			if ($expressions !== null) {
				/** @var Expr $exprNode */
				$exprNode = $expressions[0];
				/** @var \PHPStan\Type\ConstantScalarType $constantType */
				$constantType = $expressions[1];
				if ($constantType->getValue() === false) {
					$types = $this->create($exprNode, $constantType, $context);
					return $types->unionWith($this->specifyTypesInCondition(
						$scope,
						$exprNode,
						$context->true() ? TypeSpecifierContext::createFalse() : TypeSpecifierContext::createFalse()->negate()
					));
				}

				if ($constantType->getValue() === true) {
					$types = $this->create($exprNode, $constantType, $context);
					return $types->unionWith($this->specifyTypesInCondition(
						$scope,
						$exprNode,
						$context->true() ? TypeSpecifierContext::createTrue() : TypeSpecifierContext::createTrue()->negate()
					));
				}

				if ($constantType->getValue() === null) {
					return $this->create($exprNode, $constantType, $context);
				}
			}

			if ($context->true()) {
				$type = TypeCombinator::intersect($scope->getType($expr->right), $scope->getType($expr->left));
				$leftTypes = $this->create($expr->left, $type, $context);
				$rightTypes = $this->create($expr->right, $type, $context);
				return $leftTypes->unionWith($rightTypes);

			} elseif ($context->false()) {
				$identicalType = $scope->getType($expr);
				if ($identicalType instanceof ConstantBooleanType) {
					$never = new NeverType();
					$contextForTypes = $identicalType->getValue() ? $context->negate() : $context;
					$leftTypes = $this->create($expr->left, $never, $contextForTypes);
					$rightTypes = $this->create($expr->right, $never, $contextForTypes);
					return $leftTypes->unionWith($rightTypes);
				}

				if ($expr->left instanceof Node\Scalar && !$expr->right instanceof Node\Scalar) {
					return $this->create(
						$expr->right,
						$scope->getType($expr->left),
						$context
					);
				}
				if ($expr->right instanceof Node\Scalar && !$expr->left instanceof Node\Scalar) {
					return $this->create(
						$expr->left,
						$scope->getType($expr->right),
						$context
					);
				}
			}

		} elseif ($expr instanceof Node\Expr\BinaryOp\NotIdentical) {
			return $this->specifyTypesInCondition(
				$scope,
				new Node\Expr\BooleanNot(new Node\Expr\BinaryOp\Identical($expr->left, $expr->right)),
				$context
			);
		} elseif ($expr instanceof Node\Expr\BinaryOp\Equal) {
			$expressions = $this->findTypeExpressionsFromBinaryOperation($scope, $expr);
			if ($expressions !== null) {
				/** @var Expr $exprNode */
				$exprNode = $expressions[0];
				/** @var \PHPStan\Type\ConstantScalarType $constantType */
				$constantType = $expressions[1];
				if ($constantType->getValue() === false || $constantType->getValue() === null) {
					return $this->specifyTypesInCondition(
						$scope,
						$exprNode,
						$context->true() ? TypeSpecifierContext::createFalsey() : TypeSpecifierContext::createFalsey()->negate()
					);
				}

				if ($constantType->getValue() === true) {
					return $this->specifyTypesInCondition(
						$scope,
						$exprNode,
						$context->true() ? TypeSpecifierContext::createTruthy() : TypeSpecifierContext::createTruthy()->negate()
					);
				}
			}
		} elseif ($expr instanceof Node\Expr\BinaryOp\NotEqual) {
			return $this->specifyTypesInCondition(
				$scope,
				new Node\Expr\BooleanNot(new Node\Expr\BinaryOp\Equal($expr->left, $expr->right)),
				$context
			);
		} elseif ($expr instanceof FuncCall && $expr->name instanceof Name) {
			if ($this->broker->hasFunction($expr->name, $scope)) {
				$functionReflection = $this->broker->getFunction($expr->name, $scope);
				foreach ($this->getFunctionTypeSpecifyingExtensions() as $extension) {
					if (!$extension->isFunctionSupported($functionReflection, $expr, $context)) {
						continue;
					}

					return $extension->specifyTypes($functionReflection, $expr, $scope, $context);
				}
			}

			if ($defaultHandleFunctions) {
				return $this->handleDefaultTruthyOrFalseyContext($context, $expr);
			}
		} elseif ($expr instanceof MethodCall && $expr->name instanceof Node\Identifier) {
			$methodCalledOnType = $scope->getType($expr->var);
			$referencedClasses = TypeUtils::getDirectClassNames($methodCalledOnType);
			if (
				count($referencedClasses) === 1
				&& $this->broker->hasClass($referencedClasses[0])
			) {
				$methodClassReflection = $this->broker->getClass($referencedClasses[0]);
				if ($methodClassReflection->hasMethod($expr->name->name)) {
					$methodReflection = $methodClassReflection->getMethod($expr->name->name, $scope);
					foreach ($this->getMethodTypeSpecifyingExtensionsForClass($methodClassReflection->getName()) as $extension) {
						if (!$extension->isMethodSupported($methodReflection, $expr, $context)) {
							continue;
						}

						return $extension->specifyTypes($methodReflection, $expr, $scope, $context);
					}
				}
			}

			if ($defaultHandleFunctions) {
				return $this->handleDefaultTruthyOrFalseyContext($context, $expr);
			}
		} elseif ($expr instanceof StaticCall && $expr->name instanceof Node\Identifier) {
			if ($expr->class instanceof Name) {
				$calleeType = new ObjectType($scope->resolveName($expr->class));
			} else {
				$calleeType = $scope->getType($expr->class);
			}

			if ($calleeType->hasMethod($expr->name->name)) {
				$staticMethodReflection = $calleeType->getMethod($expr->name->name, $scope);
				$referencedClasses = TypeUtils::getDirectClassNames($calleeType);
				if (
					count($referencedClasses) === 1
					&& $this->broker->hasClass($referencedClasses[0])
				) {
					$staticMethodClassReflection = $this->broker->getClass($referencedClasses[0]);
					foreach ($this->getStaticMethodTypeSpecifyingExtensionsForClass($staticMethodClassReflection->getName()) as $extension) {
						if (!$extension->isStaticMethodSupported($staticMethodReflection, $expr, $context)) {
							continue;
						}

						return $extension->specifyTypes($staticMethodReflection, $expr, $scope, $context);
					}
				}
			}

			if ($defaultHandleFunctions) {
				return $this->handleDefaultTruthyOrFalseyContext($context, $expr);
			}
		} elseif ($expr instanceof BooleanAnd || $expr instanceof LogicalAnd) {
			$leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $context);
			$rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $context);
			return $context->true() ? $leftTypes->unionWith($rightTypes) : $leftTypes->intersectWith($rightTypes);
		} elseif ($expr instanceof BooleanOr || $expr instanceof LogicalOr) {
			$leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $context);
			$rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $context);
			return $context->true() ? $leftTypes->intersectWith($rightTypes) : $leftTypes->unionWith($rightTypes);
		} elseif ($expr instanceof Node\Expr\BooleanNot && !$context->null()) {
			return $this->specifyTypesInCondition($scope, $expr->expr, $context->negate());
		} elseif ($expr instanceof Node\Expr\Assign) {
			if ($context->null()) {
				return $this->specifyTypesInCondition($scope, $expr->expr, $context);
			}

			return $this->specifyTypesInCondition($scope, $expr->var, $context);
		} elseif (
			(
				$expr instanceof Expr\Isset_
				&& count($expr->vars) > 0
				&& $context->truthy()
			)
			|| ($expr instanceof Expr\Empty_ && $context->falsey())
		) {
			$vars = [];
			if ($expr instanceof Expr\Isset_) {
				$varsToIterate = $expr->vars;
			} else {
				$varsToIterate = [$expr->expr];
			}
			foreach ($varsToIterate as $var) {
				$vars[] = $var;

				while (
					$var instanceof ArrayDimFetch
					|| $var instanceof PropertyFetch
					|| (
						$var instanceof StaticPropertyFetch
						&& $var->class instanceof Expr
					)
				) {
					if ($var instanceof StaticPropertyFetch) {
						/** @var Expr $var */
						$var = $var->class;
					} else {
						$var = $var->var;
					}
					$vars[] = $var;
				}
			}

			$types = null;
			foreach ($vars as $var) {
				if ($expr instanceof Expr\Isset_) {
					if (
						$var instanceof ArrayDimFetch
						&& $var->dim !== null
						&& !$scope->getType($var->var) instanceof MixedType
					) {
						$type = $this->create(
							$var->var,
							new HasOffsetType($scope->getType($var->dim)),
							$context
						)->unionWith(
							$this->create($var, new NullType(), TypeSpecifierContext::createFalse())
						);
					} else {
						$type = $this->create($var, new NullType(), TypeSpecifierContext::createFalse());
					}
				} else {
					$type = $this->create(
						$var,
						new UnionType([
							new NullType(),
							new ConstantBooleanType(false),
						]),
						TypeSpecifierContext::createFalse()
					);
				}
				if ($types === null) {
					$types = $type;
				} else {
					$types = $types->unionWith($type);
				}
			}

			/** @var SpecifiedTypes $types */
			$types = $types;

			return $types;
		} elseif (!$context->null()) {
			return $this->handleDefaultTruthyOrFalseyContext($context, $expr);
		}

		return new SpecifiedTypes();
	}

	private function handleDefaultTruthyOrFalseyContext(TypeSpecifierContext $context, Expr $expr): SpecifiedTypes
	{
		if (!$context->truthy()) {
			$type = new ObjectWithoutClassType();
			return $this->create($expr, $type, TypeSpecifierContext::createFalse());
		} elseif (!$context->falsey()) {
			$type = new UnionType([
				new NullType(),
				new ConstantBooleanType(false),
				new ConstantIntegerType(0),
				new ConstantFloatType(0.0),
				new ConstantStringType(''),
				new ConstantArrayType([], []),
			]);
			return $this->create($expr, $type, TypeSpecifierContext::createFalse());
		}

		return new SpecifiedTypes();
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Expr\BinaryOp $binaryOperation
	 * @return (Expr|\PHPStan\Type\ConstantScalarType)[]|null
	 */
	private function findTypeExpressionsFromBinaryOperation(Scope $scope, Node\Expr\BinaryOp $binaryOperation): ?array
	{
		$leftType = $scope->getType($binaryOperation->left);
		$rightType = $scope->getType($binaryOperation->right);
		if (
			$leftType instanceof \PHPStan\Type\ConstantScalarType
			&& !$binaryOperation->right instanceof ConstFetch
			&& !$binaryOperation->right instanceof Expr\ClassConstFetch
		) {
			return [$binaryOperation->right, $leftType];
		} elseif (
			$rightType instanceof \PHPStan\Type\ConstantScalarType
			&& !$binaryOperation->left instanceof ConstFetch
			&& !$binaryOperation->left instanceof Expr\ClassConstFetch
		) {
			return [$binaryOperation->left, $rightType];
		}

		return null;
	}

	public function create(Expr $expr, Type $type, TypeSpecifierContext $context): SpecifiedTypes
	{
		if ($expr instanceof New_) {
			return new SpecifiedTypes();
		}

		$sureTypes = [];
		$sureNotTypes = [];

		$exprString = $this->printer->prettyPrintExpr($expr);
		if ($context->false()) {
			$sureNotTypes[$exprString] = [$expr, $type];
		} elseif ($context->true()) {
			$sureTypes[$exprString] = [$expr, $type];
		}

		return new SpecifiedTypes($sureTypes, $sureNotTypes);
	}

	/**
	 * @return \PHPStan\Type\FunctionTypeSpecifyingExtension[]
	 */
	public function getFunctionTypeSpecifyingExtensions(): array
	{
		return $this->functionTypeSpecifyingExtensions;
	}

	/**
	 * @param string $className
	 * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
	 */
	public function getMethodTypeSpecifyingExtensionsForClass(string $className): array
	{
		if ($this->methodTypeSpecifyingExtensionsByClass === null) {
			$byClass = [];
			foreach ($this->methodTypeSpecifyingExtensions as $extension) {
				$byClass[$extension->getClass()][] = $extension;
			}

			$this->methodTypeSpecifyingExtensionsByClass = $byClass;
		}
		return $this->getTypeSpecifyingExtensionsForType($this->methodTypeSpecifyingExtensionsByClass, $className);
	}

	/**
	 * @param string $className
	 * @return \PHPStan\Type\StaticMethodTypeSpecifyingExtension[]
	 */
	public function getStaticMethodTypeSpecifyingExtensionsForClass(string $className): array
	{
		if ($this->staticMethodTypeSpecifyingExtensionsByClass === null) {
			$byClass = [];
			foreach ($this->staticMethodTypeSpecifyingExtensions as $extension) {
				$byClass[$extension->getClass()][] = $extension;
			}

			$this->staticMethodTypeSpecifyingExtensionsByClass = $byClass;
		}
		return $this->getTypeSpecifyingExtensionsForType($this->staticMethodTypeSpecifyingExtensionsByClass, $className);
	}

	/**
	 * @param \PHPStan\Type\MethodTypeSpecifyingExtension[][]|\PHPStan\Type\StaticMethodTypeSpecifyingExtension[][] $extensions
	 * @param string $className
	 * @return mixed[]
	 */
	private function getTypeSpecifyingExtensionsForType(array $extensions, string $className): array
	{
		$extensionsForClass = [];
		$class = $this->broker->getClass($className);
		foreach (array_merge([$className], $class->getParentClassesNames(), $class->getNativeReflection()->getInterfaceNames()) as $extensionClassName) {
			if (!isset($extensions[$extensionClassName])) {
				continue;
			}

			$extensionsForClass = array_merge($extensionsForClass, $extensions[$extensionClassName]);
		}

		return $extensionsForClass;
	}

}
