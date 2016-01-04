<?php

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;

class DummyOptionalParameter implements ParameterReflection
{

	public function isOptional(): bool
	{
		return true;
	}

}
