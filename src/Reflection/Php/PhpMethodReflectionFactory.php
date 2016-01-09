<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;

interface PhpMethodReflectionFactory
{

	/**
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @param \ReflectionMethod $reflection
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param \PHPStan\Type\Type|null $phpDocReturnType
	 * @return \PHPStan\Reflection\Php\PhpMethodReflection
	 */
	public function create(
		ClassReflection $declaringClass,
		\ReflectionMethod $reflection,
		array $phpDocParameterTypes,
		Type $phpDocReturnType = null
	): PhpMethodReflection;

}
