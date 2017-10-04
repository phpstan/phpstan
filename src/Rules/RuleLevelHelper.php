<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

class RuleLevelHelper
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var bool
	 */
	private $checkNullables;

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	/**
	 * @var bool
	 */
	private $checkUnionTypes;

	public function __construct(
		Broker $broker,
		bool $checkNullables,
		bool $checkThisOnly,
		bool $checkUnionTypes
	)
	{
		$this->broker = $broker;
		$this->checkNullables = $checkNullables;
		$this->checkThisOnly = $checkThisOnly;
		$this->checkUnionTypes = $checkUnionTypes;
	}

	public function isThis(Expr $expression): bool
	{
		if (!($expression instanceof Expr\Variable)) {
			return false;
		}

		if (!is_string($expression->name)) {
			return false;
		}

		return $expression->name === 'this';
	}

	public function accepts(Type $acceptingType, Type $acceptedType): bool
	{
		if (
			!$this->checkNullables
			&& !$acceptingType instanceof NullType
			&& !$acceptedType instanceof NullType
		) {
			$acceptedType = TypeCombinator::removeNull($acceptedType);
		}

		return $acceptingType->accepts($acceptedType);
	}

	public function findTypeToCheck(
		Scope $scope,
		Expr $var,
		string $unknownClassErrorPattern
	): FoundTypeResult
	{
		if ($this->checkThisOnly && !$this->isThis($var)) {
			return new FoundTypeResult(new ErrorType(), [], []);
		}
		$type = $scope->getType($var);
		if (!$type instanceof NullType) {
			$type = \PHPStan\Type\TypeCombinator::removeNull($type);
		}
		if ($type instanceof MixedType || $type instanceof NeverType) {
			return new FoundTypeResult(new ErrorType(), [], []);
		}
		if ($type instanceof StaticType) {
			$type = $type->resolveStatic($type->getBaseClass());
		}
		if ($type instanceof ArrayType) {
			return new FoundTypeResult($type, [], []);
		}

		$errors = [];
		$referencedClasses = $type->getReferencedClasses();
		foreach ($referencedClasses as $referencedClass) {
			if (!$this->broker->hasClass($referencedClass)) {
				$errors[] = sprintf($unknownClassErrorPattern, $referencedClass);
			}
		}

		if (count($errors) > 0) {
			return new FoundTypeResult(new ErrorType(), [], $errors);
		}

		if (!$this->checkUnionTypes && $type instanceof UnionType) {
			return new FoundTypeResult(new ErrorType(), [], []);
		}

		return new FoundTypeResult($type, $referencedClasses, []);
	}

}
