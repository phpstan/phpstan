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

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
