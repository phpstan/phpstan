<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface MethodReflection extends ParametersAcceptor, ClassMemberReflection
{

	public function getPrototype(): self;

}
