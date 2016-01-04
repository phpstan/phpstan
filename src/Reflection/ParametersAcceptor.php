<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface ParametersAcceptor
{

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array;

	public function isVariadic(): bool;

}
