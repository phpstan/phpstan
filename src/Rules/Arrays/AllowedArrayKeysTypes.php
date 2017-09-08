<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\TrueOrFalseBooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class AllowedArrayKeysTypes
{

	public static function getType(): Type
	{
		return new UnionType([
			new IntegerType(),
			new StringType(),
			new FloatType(),
			new TrueOrFalseBooleanType(),
			new NullType(),
		]);
	}

}
