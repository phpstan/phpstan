<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IntegerType implements Type
{

	use JustNullableTypeTrait;

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof self) {
			return new self();
		}

		return TypeCombinator::combine($this, $otherType);
	}

	public function describe(): string
	{
		return 'int';
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return false;
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
