<?php declare(strict_types=1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;

interface PhpMethodReflectionFactory
{

	public function create(ClassReflection $declaringClass, \ReflectionMethod $reflection): PhpMethodReflection;

}
