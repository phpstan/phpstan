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

	public function doBaz(
		int $foo,
		string $bar
	)
	{
		$assertion = new \PHPStan\Tests\AssertionClass();
		$assertion::assertInt($foo);
		$assertion::assertInt($bar);
		$assertion::assertInt(1, 2);
		$assertion::assertInt(1, 2, 3);
	}

	public function doPhpunit()
	{
		\PHPUnit\Framework\Assert::assertSame(200, $this->nullableInt());
		\PHPUnit\Framework\Assert::assertSame(302, $this->nullableInt());
		\PHPUnit\Framework\Assert::assertSame(200, $this->nullableInt());
	}

	public function doPhpunitNot()
	{
		\PHPUnit\Framework\Assert::assertSame(200, $this->nullableInt());
		\PHPUnit\Framework\Assert::assertNotSame(302, $this->nullableInt());
	}

	public function nullableInt(): ?int
	{

	}

}
