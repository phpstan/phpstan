<?php

namespace ImpossibleStaticMethodCall;

class Foo
{

	public function doFoo(
		int $foo,
		string $bar
	)
	{
		\PHPStan\Tests\AssertionClass::assertInt($foo);
		\PHPStan\Tests\AssertionClass::assertInt($bar);
	}

	/**
	 * @param string|int $bar
	 */
	public function doBar($bar)
	{
		\PHPStan\Tests\AssertionClass::assertInt($bar);
	}

}
