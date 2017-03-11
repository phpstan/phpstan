<?php declare(strict_types = 1);

namespace PHPStan\TypeX;


interface ConstantType extends TypeX
{
	public function generalize(): TypeX;
}
