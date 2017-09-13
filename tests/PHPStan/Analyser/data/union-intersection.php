<?php

namespace UnionIntersection;

class WithFoo
{

	const FOO_CONSTANT = 1;

	/** @var Foo */
	public $foo;

	public function doFoo(): Foo
	{

	}

	public static function doStaticFoo(): Foo
	{

	}

}

class WithFooAndBar
{

	const FOO_CONSTANT = 1;
	const BAR_CONSTANT = 1;

	/** @var AnotherFoo */
	public $foo;

	/** @var Bar */
	public $bar;

	public function doFoo(): AnotherFoo
	{

	}

	public static function doStaticFoo(): AnotherFoo
	{

	}

	public function doBar(): Bar
	{

	}

	public static function doStaticBar(): Bar
	{

	}

}

interface WithFooAndBarInterface
{

	const FOO_CONSTANT = 1;
	const BAR_CONSTANT = 1;

	public function doFoo(): AnotherFoo;

	public static function doStaticFoo(): AnotherFoo;

	public function doBar(): Bar;

	public static function doStaticBar(): Bar;

}

interface SomeInterface
{

}

class Dolor
{

	const PARENT_CONSTANT = [1, 2, 3];

}

class Ipsum extends Dolor
{

	const IPSUM_CONSTANT = 'foo';

	/** @var WithFoo|WithFooAndBar */
	private $union;

	/** @var WithFoo|object */
	private $objectUnion;

	public function doFoo(WithFoo $foo, WithFoo $foobar, object $object)
	{
		if ($foo instanceof SomeInterface) {
			if ($foobar instanceof WithFooAndBarInterface) {
				if ($object instanceof SomeInterface) {
					die;
				}
			}
		}
	}

}
