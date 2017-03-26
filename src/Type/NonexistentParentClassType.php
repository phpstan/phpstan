<?php declare(strict_types = 1);

namespace PHPStan\Type;

class NonexistentParentClassType implements Type
{

	use JustNullableTypeTrait;

	public function describe(): string
	{
		return 'parent';
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
