<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface ParametersAcceptor
{

	public const VARIADIC_FUNCTIONS = [
		'func_get_args',
		'func_get_arg',
		'func_num_args',
	];

	/**
	 * @return array<int, \PHPStan\Reflection\ParameterReflection>
	 */
	public function getParameters(): array;

	public function isVariadic(): bool;

	public function getReturnType(): Type;

}
