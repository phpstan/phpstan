<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

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

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getIterableKeyType(): Type
	{
		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		return new ErrorType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
