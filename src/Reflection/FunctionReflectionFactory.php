<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Type\Type;

interface FunctionReflectionFactory
{

	/**
	 * @param \ReflectionFunction $reflection
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param null|Type $phpDocReturnType
	 * @param bool $isDeprecated
	 * @return PhpFunctionReflection
	 */
	public function create(
		\ReflectionFunction $reflection,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		bool $isDeprecated
	): PhpFunctionReflection;

}
