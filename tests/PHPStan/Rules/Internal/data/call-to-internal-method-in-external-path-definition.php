<?php

namespace CheckInternalMethodCallInExternalPath;

trait FooTrait
{

	public function fooFromTrait()
	{

	}

	/**
	 * @internal
	 */
	public function internalFooFromTrait()
	{

	}

}

class Foo
{

	use FooTrait;

	public function foo()
	{

	}

	/**
	 * @internal
	 */
	public function internalFoo()
	{

	}

	/**
	 * @internal
	 */
	public function internalFoo2()
	{

	}

}

class Bar extends Foo
{

	public function internalFoo()
	{

	}

}
