<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Traits\NonCallableTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\NonOffsetAccessibleTypeTrait;

class ResourceType implements Type
{

	use JustNullableTypeTrait;
	use NonCallableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use NonOffsetAccessibleTypeTrait;

	public function describe(): string
	{
		return 'resource';
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
