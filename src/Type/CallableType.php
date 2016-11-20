<?php declare(strict_types = 1);

namespace PHPStan\Type;

class CallableType implements Type
{

	use JustNullableTypeTrait;

	public function describe(): string
	{
		return 'callable';
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return true;
	}

}
