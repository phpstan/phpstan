<?php

namespace ImpossibleMethodCall;

class Foo
{

	public function doFoo(
		string $foo,
		int $bar
	)
	{
		$assertion = new \PHPStan\Tests\AssertionClass();
		$assertion->assertString($foo);
		$assertion->assertString($bar);
	}

	/**
	 * @param string|int $foo
	 */
	public function doBar($foo)
	{
		$assertion = new \PHPStan\Tests\AssertionClass();
		$assertion->assertString($foo);
	}

}
