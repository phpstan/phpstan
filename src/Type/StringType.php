<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\NonOffsetAccessibleTypeTrait;

class StringType implements Type
{

	use JustNullableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use NonOffsetAccessibleTypeTrait;

	public function describe(): string
	{
		return 'string';
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getOffsetValueType(): Type
	{
		return new StringType();
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
