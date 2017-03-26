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

		if ($otherType instanceof FloatType) {
			return new FloatType();
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

}
