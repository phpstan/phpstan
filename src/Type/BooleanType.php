<?php declare(strict_types = 1);

namespace PHPStan\Type;

class BooleanType implements Type
{

	use JustNullableTypeTrait;

	public function describe(): string
	{
		return 'bool';
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
