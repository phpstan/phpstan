<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class IntegerType implements Type
{

	use JustNullableTypeTrait;

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

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
