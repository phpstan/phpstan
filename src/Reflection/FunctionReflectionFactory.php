<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface FunctionReflectionFactory
{

	public function create(\ReflectionFunction $reflection): FunctionReflection;

}
