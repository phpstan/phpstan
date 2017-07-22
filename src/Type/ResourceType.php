<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ResourceType implements Type
{

	use JustNullableTypeTrait;

	public function describe(): string
	{
		return 'resource';
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
