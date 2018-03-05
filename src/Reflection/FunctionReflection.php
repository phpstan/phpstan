<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface FunctionReflection extends ParametersAcceptor
{

	public function getName(): string;

}
