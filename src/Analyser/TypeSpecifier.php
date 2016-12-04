<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableIterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;

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
			if ($class === 'static') {
				return $types;
			}

			if ($class === 'self' && $scope->getClass() !== null) {
				$class = $scope->getClass();
			}

			$printedExpr = $this->printer->prettyPrintExpr($expr->expr);
			$objectType = new ObjectType($class, false);
			if ($negated) {
				if ($source === self::SOURCE_FROM_AND) {
					return $types;
				}
				return $types->addSureNotType($expr->expr, $printedExpr, $objectType);
			}

			return $types->addSureType($expr->expr, $printedExpr, $objectType);
		} elseif (
			$expr instanceof FuncCall
			&& $expr->name instanceof Name
			&& isset($expr->args[0])
		) {
			$functionName = (string) $expr->name;
			$argumentExpression = $expr->args[0]->value;
			$specifiedType = null;
			if (in_array($functionName, [
				'is_int',
				'is_integer',
				'is_long',
			], true)) {
				$specifiedType = new IntegerType(false);
			} elseif (in_array($functionName, [
				'is_float',
				'is_double',
				'is_real',
			], true)) {
				$specifiedType = new FloatType(false);
			} elseif ($functionName === 'is_null') {
				$specifiedType = new NullType();
			} elseif ($functionName === 'is_array') {
				$specifiedType = new ArrayType(new MixedType(true), false);
			} elseif ($functionName === 'is_bool') {
				$specifiedType = new BooleanType(false);
			} elseif ($functionName === 'is_callable') {
				$specifiedType = new CallableType(false);
			} elseif ($functionName === 'is_resource') {
				$specifiedType = new ResourceType(false);
			} elseif ($functionName === 'is_iterable') {
				$specifiedType = new IterableIterableType(new MixedType(true), false);
			}

			if ($specifiedType !== null) {
				$printedExpr = $this->printer->prettyPrintExpr($argumentExpression);

				if ($negated) {
					return $types->addSureNotType($argumentExpression, $printedExpr, $specifiedType);
				}

				return $types->addSureType($argumentExpression, $printedExpr, $specifiedType);
			}
		} elseif ($expr instanceof BooleanAnd) {
			if ($source !== self::SOURCE_UNKNOWN && $source !== self::SOURCE_FROM_AND) {
				return $types;
			}
			$types = $this->specifyTypesInCondition($types, $scope, $expr->left, $negated, self::SOURCE_FROM_AND);
			$types = $this->specifyTypesInCondition($types, $scope, $expr->right, $negated, self::SOURCE_FROM_AND);
		} elseif ($expr instanceof BooleanOr) {
			if ($negated) {
				return $types;
			}
			$types = $this->specifyTypesInCondition($types, $scope, $expr->left, $negated, self::SOURCE_FROM_OR);
			$types = $this->specifyTypesInCondition($types, $scope, $expr->right, $negated, self::SOURCE_FROM_OR);
		} elseif ($expr instanceof Node\Expr\BooleanNot) {
			if ($source === self::SOURCE_FROM_AND) {
				return $types;
			}

			$types = $this->specifyTypesInCondition($types, $scope, $expr->expr, !$negated, $source);
		}

		return $types;
	}

}
