<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface FunctionReflection extends DeprecatableReflection, InternableReflection, FinalizableReflection, ThrowableReflection
{

	public function getName(): string;

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array;

}
