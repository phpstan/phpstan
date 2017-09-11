<?php

namespace UnionIntersection;

class WithFoo
{

	/** @var Foo */
	public $foo;

	public function doFoo(): Foo
	{

	}

}

class WithFooAndBar
{

	/** @var AnotherFoo */
	public $foo;

	/** @var Bar */
	public $bar;

	public function doFoo(): AnotherFoo
	{

	}

	public function doBar(): Bar
	{

	}

}

interface SomeInterface
{

}

class Ipsum
{

	/** @var WithFoo|WithFooAndBar */
	private $union;

	public function doFoo(WithFoo $foo)
	{
		if ($foo instanceof SomeInterface) {
			die;
		}
	}

}
