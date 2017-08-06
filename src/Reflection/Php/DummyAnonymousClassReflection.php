<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

class DummyAnonymousClassReflection extends \ReflectionClass
{

	public function isAnonymous(): bool
	{
		return true;
	}

}
