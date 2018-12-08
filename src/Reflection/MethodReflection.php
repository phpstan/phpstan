<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface MethodReflection extends ClassMemberReflection
{

	public function getName(): string;

	public function getPrototype(): ClassMemberReflection;

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array;

}
