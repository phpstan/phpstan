<?php

namespace MissingReturn;

class Foo
{

	public function doFoo(): int
	{

	}

	public function doBar(): int
	{
		// noop comment
	}

	public function doBaz(): int
	{
		doFoo();
		doBar();
	}

	public function doLorem(): int
	{
		if (doFoo()) {

		}

		try {

		} catch (\Exception $e) {

		}

		if (doFoo()) {
			doFoo();
			doBar();
			if (doFoo()) {

			} elseif (blabla()) {

			} else {

			}
		} else {
			try {

			} catch (\Exception $e) {

			}
		}
	}

}

class Bar
{

	public function doFoo(): int
	{
		return 1;
	}

	public function doBar(): int
	{
		doFoo();

		return 1;
	}

	public function doBaz(): void
	{

	}

	public function doLorem(): void
	{
		doFoo();
	}

	public function doIpsum()
	{

	}

	public function doDolor()
	{
		doFoo();
	}

	public function doSit(): iterable
	{
		doBar();
		doFoo(yield 1);
	}

}

class Baz
{

	public function doFoo()
	{
		function (): int {

		};
	}

}

function doFoo(): int
{

}
