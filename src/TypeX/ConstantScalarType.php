<?php declare(strict_types = 1);

namespace PHPStan\TypeX;


interface ConstantScalarType extends ConstantType
{
	public function getValue();
}
