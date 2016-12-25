<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

interface FunctionReflectionFactory
{

	public function create(
		\ReflectionFunction $reflection,
		array $phpDocParameterTypes,
		Type $phpDocReturnType = null
	): FunctionReflection;

}
