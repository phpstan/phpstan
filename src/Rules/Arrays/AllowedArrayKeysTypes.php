<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Type\CommonUnionType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\TrueOrFalseBooleanType;
use PHPStan\Type\Type;

class AllowedArrayKeysTypes
{

	public static function getType(): Type
	{
		return new CommonUnionType([
			new IntegerType(),
			new StringType(),
			new FloatType(),
			new TrueOrFalseBooleanType(),
			new NullType(),
		]);
	}

}
