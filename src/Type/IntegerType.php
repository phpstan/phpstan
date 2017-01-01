<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IntegerType implements Type
{

	use JustNullableTypeTrait;

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof self) {
			return new self($this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof FloatType) {
			return new FloatType($this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType();
	}

	public function describe(): string
	{
		return 'int' . ($this->nullable ? '|null' : '');
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
