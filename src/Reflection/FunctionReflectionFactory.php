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
	 * @param null|Type $phpDocThrowType
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 * @return PhpFunctionReflection
	 */
	public function create(
		\ReflectionFunction $reflection,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $phpDocThrowType,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	): PhpFunctionReflection;

}
