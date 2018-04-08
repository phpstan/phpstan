<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Traits\NonCallableTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\NonOffsetAccessibleTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;

class BooleanType implements Type
{

	use JustNullableTypeTrait;
	use NonCallableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use NonOffsetAccessibleTypeTrait;
	use UndecidedBooleanTypeTrait;

	public function describe(): string
	{
		return 'bool';
	}

	public function toNumber(): Type
	{
		return $this->toInteger();
	}

	public function toString(): Type
	{
		return TypeCombinator::union(
			new ConstantStringType(''),
			new ConstantStringType('1')
		);
	}

	public function toInteger(): Type
	{
		return TypeCombinator::union(
			new ConstantIntegerType(0),
			new ConstantIntegerType(1)
		);
	}

	public function toFloat(): Type
	{
		return TypeCombinator::union(
			new ConstantFloatType(0.0),
			new ConstantFloatType(1.0)
		);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new static();
	}

}
