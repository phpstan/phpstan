<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;

class IntegerType implements Type
{

	use JustNullableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;

	public function describe(): string
	{
		return 'int';
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
