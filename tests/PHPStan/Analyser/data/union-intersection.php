<?php

namespace UnionIntersection;

class WithFooProperty
{

	/** @var Foo */
	public $foo;

}

class WithFooAndBarProperty
{

	/** @var AnotherFoo */
	public $foo;

	/** @var Bar */
	public $bar;

}

interface SomeInterface
{

}

class Ipsum
{

	/** @var WithFooAndBarProperty|WithFooProperty */
	private $union;

	public function doFoo(WithFooProperty $foo)
	{
		if ($foo instanceof SomeInterface) {
			die;
		}
	}

}
