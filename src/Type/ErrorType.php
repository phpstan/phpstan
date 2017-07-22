<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ErrorType extends MixedType
{

	public function getIterableKeyType(): Type
	{
		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		return new ErrorType();
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
