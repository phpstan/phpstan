<?php declare(strict_types = 1);

namespace PHPStan\Type;

class StringType implements Type
{

	use JustNullableTypeTrait;

	public function describe(): string
	{
		return 'string';
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return false;
	}

	public function isIterable(): int
	{
		return TrinaryLogic::NO;
	}

	public function getIterableKeyType(): Type
	{
		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		return new ErrorType();
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
