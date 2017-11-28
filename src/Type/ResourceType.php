<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\NonObjectTypeTrait;

class ResourceType implements Type
{

	use JustNullableTypeTrait;
	use NonObjectTypeTrait;

	public function describe(): string
	{
		return 'resource';
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
