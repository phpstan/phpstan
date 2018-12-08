<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class TrivialParametersAcceptor implements ParametersAcceptor
{

	/**
	 * @return array<int, \PHPStan\Reflection\ParameterReflection>
	 */
	public function getParameters(): array
	{
		return [];
	}

	public function isVariadic(): bool
	{
		return true;
	}

	public function getReturnType(): Type
	{
		return new MixedType();
	}

}
