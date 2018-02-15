<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface FunctionReflection extends ParametersAcceptor
{

	public function getName(): string;

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array;

	public function isVariadic(): bool;

	public function getReturnType(): Type;

}
