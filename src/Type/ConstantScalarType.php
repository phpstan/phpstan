<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface ConstantScalarType extends ConstantType
{

	/**
	 * @return int|float|string|bool|null
	 */
	public function getValue();

}
