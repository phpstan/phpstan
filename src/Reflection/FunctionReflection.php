<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface FunctionReflection extends ParametersAcceptor, DeprecatableReflection, ThrowableReflection
{

	public function getName(): string;

}
