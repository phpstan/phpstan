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
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

class TypeSpecifier
{

	private const CONTEXT_TRUE = 0b0001;
	private const CONTEXT_TRUTHY_BUT_NOT_TRUE = 0b0010;
	public const CONTEXT_TRUTHY = self::CONTEXT_TRUE | self::CONTEXT_TRUTHY_BUT_NOT_TRUE;
	private const CONTEXT_FALSE = 0b0100;
	private const CONTEXT_FALSEY_BUT_NOT_FALSE = 0b1000;
	public const CONTEXT_FALSEY = self::CONTEXT_FALSE | self::CONTEXT_FALSEY_BUT_NOT_FALSE;

	/**
	 * @var \PhpParser\PrettyPrinter\Standard
	 */
	private $printer;

	public function __construct(\PhpParser\PrettyPrinter\Standard $printer)
	{
		$this->printer = $printer;
	}

	public function specifyTypesInCondition(Scope $scope, Expr $expr, int $context = self::CONTEXT_TRUTHY): SpecifiedTypes
	{
		if ($expr instanceof Instanceof_) {
			if ($expr->class instanceof Name) {
				$className = (string) $expr->class;
				if ($className === 'self' && $scope->isInClass()) {
					$type = new ObjectType($scope->getClassReflection()->getName());
				} elseif ($className === 'static' && $scope->isInClass()) {
					$type = new StaticType($scope->getClassReflection()->getName());
				} else {
					$type = new ObjectType($className);
				}
				return $this->create($expr->expr, $type, $context);
			} elseif ($context & self::CONTEXT_TRUE) {
				return $this->create($expr->expr, new ObjectWithoutClassType(), $context);
			}
		} elseif ($expr instanceof Node\Expr\BinaryOp\Identical) {
			$expressions = $this->findTypeExpressionsFromBinaryOperation($expr);
			if ($expressions !== null) {
				$constantName = strtolower((string) $expressions[1]->name);
				if ($constantName === 'false') {
					$types = $this->create($expressions[0], new ConstantBooleanType(false), $context);
					return $types->unionWith($this->specifyTypesInCondition(
						$scope,
						$expressions[0],
						($context & self::CONTEXT_TRUE) ? self::CONTEXT_FALSE : ~self::CONTEXT_FALSE
					));
				} elseif ($constantName === 'true') {
					$types = $this->create($expressions[0], new ConstantBooleanType(true), $context);
					return $types->unionWith($this->specifyTypesInCondition(
						$scope,
						$expressions[0],
						($context & self::CONTEXT_TRUE) ? self::CONTEXT_TRUE : ~self::CONTEXT_TRUE
					));
				} elseif ($constantName === 'null') {
					return $this->create($expressions[0], new NullType(), $context);
				}
			}

			if ($context & self::CONTEXT_TRUE) {
				$type = TypeCombinator::intersect($scope->getType($expr->right), $scope->getType($expr->left));
				$leftTypes = $this->create($expr->left, $type, $context);
				$rightTypes = $this->create($expr->right, $type, $context);
				return $leftTypes->unionWith($rightTypes);

			} elseif ($context & self::CONTEXT_FALSE) {
				$type = TypeCombinator::intersect($scope->getType($expr->right), $scope->getType($expr->left));
				if ($type instanceof ConstantScalarType) {
					$leftTypes = $this->create($expr->left, $type, $context);
					$rightTypes = $this->create($expr->right, $type, $context);
					return $leftTypes->unionWith($rightTypes);
				}
			}

		} elseif ($expr instanceof Node\Expr\BinaryOp\NotIdentical) {
			return $this->specifyTypesInCondition(
				$scope,
				new Node\Expr\BooleanNot(new Node\Expr\BinaryOp\Identical($expr->left, $expr->right)),
				$context
			);
		} elseif ($expr instanceof Node\Expr\BinaryOp\Equal) {
			$expressions = $this->findTypeExpressionsFromBinaryOperation($expr);
			if ($expressions !== null) {
				$constantName = strtolower((string) $expressions[1]->name);
				if ($constantName === 'false' || $constantName === 'null') {
					return $this->specifyTypesInCondition(
						$scope,
						$expressions[0],
						($context & self::CONTEXT_TRUE) ? self::CONTEXT_FALSEY : ~self::CONTEXT_FALSEY
					);
				} elseif ($constantName === 'true') {
					return $this->specifyTypesInCondition(
						$scope,
						$expressions[0],
						($context & self::CONTEXT_TRUE) ? self::CONTEXT_TRUTHY : ~self::CONTEXT_TRUTHY
					);
				}
			}
		} elseif ($expr instanceof Node\Expr\BinaryOp\NotEqual) {
			return $this->specifyTypesInCondition(
				$scope,
				new Node\Expr\BooleanNot(new Node\Expr\BinaryOp\Equal($expr->left, $expr->right)),
				$context
			);
		} elseif (
			$expr instanceof FuncCall
			&& $expr->name instanceof Name
			&& isset($expr->args[0])
		) {
			$functionName = strtolower((string) $expr->name);
			$innerExpr = $expr->args[0]->value;
			switch ($functionName) {
				case 'is_int':
				case 'is_integer':
				case 'is_long':
					return $this->create($innerExpr, new IntegerType(), $context);
				case 'is_float':
				case 'is_double':
				case 'is_real':
					return $this->create($innerExpr, new FloatType(), $context);
				case 'is_null':
					return $this->create($innerExpr, new NullType(), $context);
				case 'is_array':
					return $this->create($innerExpr, new ArrayType(new MixedType(), new MixedType(), false), $context);
				case 'is_bool':
					return $this->create($innerExpr, new BooleanType(), $context);
				case 'is_callable':
					return $this->create($innerExpr, new CallableType(), $context);
				case 'is_resource':
					return $this->create($innerExpr, new ResourceType(), $context);
				case 'is_iterable':
					return $this->create($innerExpr, new IterableType(new MixedType(), new MixedType()), $context);
				case 'is_string':
					return $this->create($innerExpr, new StringType(), $context);
				case 'is_object':
					return $this->create($innerExpr, new ObjectWithoutClassType(), $context);
				case 'is_numeric':
					return $this->create($innerExpr, new UnionType([
						new StringType(),
						new IntegerType(),
						new FloatType(),
					]), $context);
				case 'is_a':
					if (isset($expr->args[1])) {
						$classNameArgExpr = $expr->args[1]->value;
						$classNameArgExprType = $scope->getType($classNameArgExpr);
						if (
							$classNameArgExpr instanceof Expr\ClassConstFetch
							&& $classNameArgExpr->class instanceof Name
							&& is_string($classNameArgExpr->name)
							&& strtolower($classNameArgExpr->name) === 'class'
						) {
							$className = $scope->resolveName($classNameArgExpr->class);
							if (strtolower($classNameArgExpr->class->toString()) === 'static') {
								$objectType = new StaticType($className);
							} else {
								$objectType = new ObjectType($className);
							}
							$types = $this->create($innerExpr, $objectType, $context);
						} elseif ($classNameArgExprType instanceof ConstantStringType) {
							$objectType = new ObjectType($classNameArgExprType->getValue());
							$types = $this->create($innerExpr, $objectType, $context);
						} elseif ($context & self::CONTEXT_TRUE) {
							$objectType = new ObjectWithoutClassType();
							$types = $this->create($innerExpr, $objectType, $context);
						} else {
							$types = new SpecifiedTypes();
						}

						if (isset($expr->args[2]) && ($context & self::CONTEXT_TRUE)) {
							if (!$scope->getType($expr->args[2]->value)->isSuperTypeOf(new ConstantBooleanType(true))->no()) {
								$types = $types->intersectWith($this->create($innerExpr, new StringType(), $context));
							}
						}

						return $types;
					}
			}
		} elseif ($expr instanceof BooleanAnd || $expr instanceof LogicalAnd) {
			$leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $context);
			$rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $context);
			return ($context & self::CONTEXT_TRUE) ? $leftTypes->unionWith($rightTypes) : $leftTypes->intersectWith($rightTypes);
		} elseif ($expr instanceof BooleanOr || $expr instanceof LogicalOr) {
			$leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $context);
			$rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $context);
			return ($context & self::CONTEXT_TRUE) ? $leftTypes->intersectWith($rightTypes) : $leftTypes->unionWith($rightTypes);
		} elseif ($expr instanceof Node\Expr\BooleanNot) {
			return $this->specifyTypesInCondition($scope, $expr->expr, ~$context);
		} elseif ($expr instanceof Node\Expr\Assign) {
			return $this->specifyTypesInCondition($scope, $expr->var, $context);
		} elseif (
			(
				$expr instanceof Expr\Isset_
				&& count($expr->vars) > 0
				&& $context & self::CONTEXT_TRUTHY
			)
			|| ($expr instanceof Expr\Empty_ && $context & self::CONTEXT_FALSEY)
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
				) {
					$var = $var->var;
					$vars[] = $var;
				}

				while (
					$var instanceof StaticPropertyFetch
					&& $var->class instanceof Expr
				) {
					$var = $var->class;
					$vars[] = $var;
				}
			}

			$types = null;
			foreach ($vars as $var) {
				if ($expr instanceof Expr\Isset_) {
					$type = $this->create($var, new NullType(), self::CONTEXT_FALSE);
				} else {
					$type = $this->create(
						$var,
						new UnionType([
							new NullType(),
							new ConstantBooleanType(false),
						]),
						self::CONTEXT_FALSE
					);
				}
				if ($types === null) {
					$types = $type;
				} else {
					$types = $types->unionWith($type);
				}
			}
			return $types;
		} elseif (($context & self::CONTEXT_TRUTHY) === 0) {
			$type = new ObjectWithoutClassType();
			return $this->create($expr, $type, self::CONTEXT_FALSE);
		} elseif (($context & self::CONTEXT_FALSEY) === 0) {
			$type = new UnionType([
				new NullType(),
				new ConstantBooleanType(false),
				new ConstantIntegerType(0),
//				new ConstantFloatType(0.0),
//				new ConstantStringType(''),
//				new ConstantArrayType([], []),
			]);
			return $this->create($expr, $type, self::CONTEXT_FALSE);
		}

		return new SpecifiedTypes();
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp $binaryOperation
	 * @return array|null
	 */
	private function findTypeExpressionsFromBinaryOperation(Node\Expr\BinaryOp $binaryOperation): ?array
	{
		if ($binaryOperation->left instanceof ConstFetch) {
			return [$binaryOperation->right, $binaryOperation->left];
		} elseif ($binaryOperation->right instanceof ConstFetch) {
			return [$binaryOperation->left, $binaryOperation->right];
		}

		return null;
	}

	private function create(Expr $expr, Type $type, int $context): SpecifiedTypes
	{
		$sureTypes = [];
		$sureNotTypes = [];

		$exprString = $this->printer->prettyPrintExpr($expr);
		if ($context & self::CONTEXT_FALSE) {
			$sureNotTypes[$exprString] = [$expr, $type];
		} elseif ($context & self::CONTEXT_TRUE) {
			$sureTypes[$exprString] = [$expr, $type];
		}

		return new SpecifiedTypes($sureTypes, $sureNotTypes);
	}

}
