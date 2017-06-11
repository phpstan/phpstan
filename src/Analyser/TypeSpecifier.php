<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CallableType;
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
use PHPStan\Type\TrueOrFalseBooleanType;
use PHPStan\Type\Type;

class TypeSpecifier
{

	const SOURCE_UNKNOWN = 0;
	const SOURCE_FROM_AND = 1;
	const SOURCE_FROM_OR = 2;

	/**
	 * @var \PhpParser\PrettyPrinter\Standard
	 */
	private $printer;

	public function __construct(\PhpParser\PrettyPrinter\Standard $printer)
	{
		$this->printer = $printer;
	}

	public function specifyTypesInCondition(
		SpecifiedTypes $types,
		Scope $scope,
		Node $expr,
		bool $negated = false,
		int $source = self::SOURCE_UNKNOWN
	): SpecifiedTypes
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

			return $this->apply($types, $expr->expr, $negated, $source, $type);
		} elseif ($expr instanceof Node\Expr\BinaryOp\Identical) {
			$expressions = $this->findTypeExpressionsFromBinaryOperation($expr);
			if ($expressions === null) {
				return $types;
			}
			if (!in_array(strtolower((string) $expressions[1]->name), [
				'null',
				'true',
				'false',
			], true)) {
				return $types;
			}
			$sureType = $scope->getType($expressions[1]);
			return $this->apply($types, $expressions[0], $negated, $source, $sureType);
		} elseif ($expr instanceof Node\Expr\BinaryOp\NotIdentical) {
			return $this->specifyTypesInCondition(
				$types,
				$scope,
				new Node\Expr\BooleanNot(
					new Node\Expr\BinaryOp\Identical(
						$expr->left,
						$expr->right
					)
				),
				$negated,
				$source
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
				return $this->apply($types, $argumentExpression, $negated, $source, $specifiedType);
			}
		} elseif ($expr instanceof BooleanAnd) {
			if ($negated) {
				$leftTypes = $this->specifyTypesInCondition($types, $scope, $expr->left, $negated, $source);
				$rightTypes = $this->specifyTypesInCondition($types, $scope, $expr->right, $negated, $source);
				$types = $leftTypes->unionWith($rightTypes);
			} else {
				$types = $this->specifyTypesInCondition($types, $scope, $expr->left, $negated, self::SOURCE_FROM_AND);
				$types = $this->specifyTypesInCondition($types, $scope, $expr->right, $negated, self::SOURCE_FROM_AND);
			}
		} elseif ($expr instanceof BooleanOr) {
			if ($negated) {
				$types = $this->specifyTypesInCondition($types, $scope, $expr->left, $negated, self::SOURCE_FROM_OR);
				$types = $this->specifyTypesInCondition($types, $scope, $expr->right, $negated, self::SOURCE_FROM_OR);
			} else {
				$leftTypes = $this->specifyTypesInCondition($types, $scope, $expr->left, $negated, $source);
				$rightTypes = $this->specifyTypesInCondition($types, $scope, $expr->right, $negated, $source);
				$types = $leftTypes->unionWith($rightTypes);
			}

		} elseif ($expr instanceof Node\Expr\BooleanNot) {
			if ($source === self::SOURCE_FROM_AND) {
				$types = $this->specifyTypesInCondition($types, $scope, $expr->expr, !$negated, self::SOURCE_FROM_OR);
			} elseif ($source === self::SOURCE_FROM_OR) {
				$types = $this->specifyTypesInCondition($types, $scope, $expr->expr, !$negated, self::SOURCE_FROM_AND);
			} else {
				$types = $this->specifyTypesInCondition($types, $scope, $expr->expr, !$negated, $source);
			}

		} elseif ($expr instanceof Node\Expr\Variable && is_string($expr->name)) {
			if ($negated && $source !== self::SOURCE_FROM_AND) {
				if ($scope->hasVariableType($expr->name)) {
					$className = $scope->getVariableType($expr->name)->getClass();
					if ($className !== null) {
						$printedExpr = $this->printer->prettyPrintExpr($expr);
						$types = $types->addSureNotType($expr, $printedExpr, new ObjectType($className));
					}
				}
			} elseif (!$negated && $source !== self::SOURCE_FROM_OR) {
				$printedExpr = $this->printer->prettyPrintExpr($expr);
				$types = $types->addSureNotType($expr, $printedExpr, new NullType());
				$types = $types->addSureNotType($expr, $printedExpr, new FalseBooleanType());
			}
		}

		return $types;
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

	private function apply(SpecifiedTypes $types, Node\Expr $expr, bool $negated, int $source, Type $type): SpecifiedTypes
	{
		$printedExpr = $this->printer->prettyPrintExpr($expr);

		if ($negated && $source !== self::SOURCE_FROM_AND) {
			$types = $types->addSureNotType($expr, $printedExpr, $type);
		} elseif (!$negated && $source !== self::SOURCE_FROM_OR) {
			$types = $types->addSureType($expr, $printedExpr, $type);
		}

		return $types;
	}

}
