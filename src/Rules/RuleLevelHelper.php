<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\TypeX\TypeXFactory;


class RuleLevelHelper
{

	/** @var bool */
	private $checkNullables;

	public function __construct(bool $checkNullables)
	{
		$this->checkNullables = $checkNullables;
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
		$typeFactory = TypeXFactory::getInstance();

		if (
			!$this->checkNullables
			&& !$acceptingType instanceof NullType
			&& !$acceptedType instanceof NullType
		) {

			$acceptedType = $typeFactory->createIntersectionType(
				$typeFactory->createFromLegacy($acceptedType),
				$typeFactory->createComplementType($typeFactory->createNullType())
			);
		}

		return $typeFactory->createFromLegacy($acceptingType)->accepts($acceptedType);
	}

}
