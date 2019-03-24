<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class SimpleXMLElementProperty extends UniversalObjectCrateProperty
{

	public function getWriteableType(): Type
	{
		return TypeCombinator::union(
			parent::getWriteableType(),
			new IntegerType(),
			new FloatType(),
			new StringType(),
			new BooleanType()
		);
	}


	public function canChangeTypeAfterAssignment(): bool
	{
		return false;
	}

}
