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

	public function doBaz(int $foo)
	{
		$assertion = new \PHPStan\Tests\AssertionClass();
		$assertion->assertNotInt($foo);
	}

	public function doLorem(string $foo)
	{
		$assertion = new \PHPStan\Tests\AssertionClass();
		$assertion->assertNotInt($foo);
	}

	/**
	 * @param string|int $foo
	 */
	public function doIpsum($foo)
	{
		$assertion = new \PHPStan\Tests\AssertionClass();
		$assertion->assertNotInt($foo);
	}

}
