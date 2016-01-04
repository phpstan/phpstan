<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface MethodReflection extends ParametersAcceptor
{

	public function getDeclaringClass(): ClassReflection;

	public function isStatic(): bool;

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array;

	public function isVariadic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

}
