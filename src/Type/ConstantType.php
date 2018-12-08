<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface ConstantType extends Type
{

	public function generalize(): Type;

}
