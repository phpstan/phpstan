<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IntegerType implements Type
{

	use JustNullableTypeTrait;

	public function describe(): string
	{
		return 'int';
	}

}
