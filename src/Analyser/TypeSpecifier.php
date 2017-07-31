<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CallableType;
use PHPStan\Type\CommonUnionType;
use PHPStan\Type\FalseBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableIterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\TrueBooleanType;
use PHPStan\Type\TrueOrFalseBooleanType;
use PHPStan\Type\Type;

class TypeSpecifier
{

	/**
	 * @var \PhpParser\PrettyPrinter\Standard
	 */
	private $printer;

	public function __construct(\PhpParser\PrettyPrinter\Standard $printer)
	{
		$this->printer = $printer;
	}

	public function specifyTypesInCondition(Scope $scope, Expr $expr, bool $negated = false): SpecifiedTypes
	{
		if ($expr instanceof Instanceof_ && $expr->class instanceof Name) {
			$class = (string) $expr->class;
			if ($class === 'self' && $scope->isInClass()) {
				$type = new ObjectType($scope->getClassReflection()->getName());
			} elseif ($class === 'static' && $scope->isInClass()) {
				$type = new StaticType($scope->getClassReflection()->getName());
			} else {
				$type = new ObjectType($class);
			}

			return $this->create($expr->expr, $type, $negated);
		} elseif ($expr instanceof Node\Expr\BinaryOp\Identical) {
			$expressions = $this->findTypeExpressionsFromBinaryOperation($expr);
			if ($expressions !== null) {
				$constantName = strtolower((string) $expressions[1]->name);
				if ($constantName === 'false') {
					$types = $this->specifyTypesInCondition($scope, $expressions[0], !$negated);
					return $types->unionWith($this->create($expressions[0], new FalseBooleanType(), $negated));
				} elseif ($constantName === 'true') {
					$types = $this->specifyTypesInCondition($scope, $expressions[0], $negated);
					return $types->unionWith($this->create($expressions[0], new TrueBooleanType(), $negated));
				} elseif ($constantName === 'null') {
					return $this->create($expressions[0], new NullType(), $negated);
				}
			}
		} elseif ($expr instanceof Node\Expr\BinaryOp\NotIdentical) {
			return $this->specifyTypesInCondition(
				$scope,
				new Node\Expr\BinaryOp\Identical($expr->left, $expr->right),
				!$negated
			);
		} elseif ($expr instanceof Node\Expr\BinaryOp\Equal) {
			$expressions = $this->findTypeExpressionsFromBinaryOperation($expr);
			if ($expressions !== null) {
				$constantName = strtolower((string) $expressions[1]->name);
				if ($constantName === 'false') {
					return $this->specifyTypesInCondition($scope, $expressions[0], !$negated);
				} elseif ($constantName === 'true') {
					return $this->specifyTypesInCondition($scope, $expressions[0], $negated);
				}
			}
		} elseif ($expr instanceof Node\Expr\BinaryOp\NotEqual) {
			return $this->specifyTypesInCondition(
				$scope,
				new Node\Expr\BinaryOp\Equal($expr->left, $expr->right),
				!$negated
			);
		} elseif (
			$expr instanceof FuncCall
			&& $expr->name instanceof Name
			&& isset($expr->args[0])
		) {
			$functionName = strtolower((string) $expr->name);
			$argumentExpression = $expr->args[0]->value;
			$specifiedType = null;
			if (in_array($functionName, [
				'is_int',
				'is_integer',
				'is_long',
			], true)) {
				$specifiedType = new IntegerType();
			} elseif (in_array($functionName, [
				'is_float',
				'is_double',
				'is_real',
			], true)) {
				$specifiedType = new FloatType();
			} elseif ($functionName === 'is_null') {
				$specifiedType = new NullType();
			} elseif ($functionName === 'is_array' && !($scope->getType($argumentExpression) instanceof ArrayType)) {
				$specifiedType = new ArrayType(new MixedType());
			} elseif ($functionName === 'is_bool') {
				$specifiedType = new TrueOrFalseBooleanType();
			} elseif ($functionName === 'is_callable') {
				$specifiedType = new CallableType();
			} elseif ($functionName === 'is_resource') {
				$specifiedType = new ResourceType();
			} elseif ($functionName === 'is_iterable') {
				$specifiedType = new IterableIterableType(new MixedType());
			} elseif ($functionName === 'is_string') {
				$specifiedType = new StringType();
			}

			if ($specifiedType !== null) {
				return $this->create($argumentExpression, $specifiedType, $negated);
			}
		} elseif ($expr instanceof BooleanAnd) {
			$leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $negated);
			$rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $negated);
			return $negated ? $leftTypes->intersectWith($rightTypes) : $leftTypes->unionWith($rightTypes);
		} elseif ($expr instanceof BooleanOr) {
			$leftTypes = $this->specifyTypesInCondition($scope, $expr->left, $negated);
			$rightTypes = $this->specifyTypesInCondition($scope, $expr->right, $negated);
			return $negated ? $leftTypes->unionWith($rightTypes) : $leftTypes->intersectWith($rightTypes);
		} elseif ($expr instanceof Node\Expr\BooleanNot) {
			return $this->specifyTypesInCondition($scope, $expr->expr, !$negated);
		} elseif ($negated) {
			$className = $scope->getType($expr)->getClass();
			if ($className !== null) {
				return $this->create($expr, new ObjectType($className), true);
			}
		} else {
			return $this->create(
				$expr,
				new CommonUnionType([new NullType(), new FalseBooleanType()]),
				true
			);
		}

		return new SpecifiedTypes();
	}

	/**
	 * @param \PhpParser\Node\Expr\BinaryOp $binaryOperation
	 * @return array|null
	 */
	private function findTypeExpressionsFromBinaryOperation(Node\Expr\BinaryOp $binaryOperation)
	{
		if ($binaryOperation->left instanceof ConstFetch) {
			return [$binaryOperation->right, $binaryOperation->left];
		} elseif ($binaryOperation->right instanceof ConstFetch) {
			return [$binaryOperation->left, $binaryOperation->right];
		}

		return null;
	}

	private function create(Expr $expr, Type $type, bool $negated): SpecifiedTypes
	{
		$sureTypes = [];
		$sureNotTypes = [];

		if ($expr instanceof Node\Expr\Variable
			|| $expr instanceof Node\Expr\FuncCall
			|| $expr instanceof Node\Expr\MethodCall
			|| $expr instanceof Node\Expr\StaticCall
			|| $expr instanceof Node\Expr\PropertyFetch
			|| $expr instanceof Node\Expr\StaticPropertyFetch
			|| $expr instanceof Node\Expr\ArrayDimFetch
		) {
			$exprString = $this->printer->prettyPrintExpr($expr);
			if ($negated) {
				$sureNotTypes[$exprString] = [$expr, $type];
			} else {
				$sureTypes[$exprString] = [$expr, $type];
			}
		}

		return new SpecifiedTypes($sureTypes, $sureNotTypes);
	}

}
