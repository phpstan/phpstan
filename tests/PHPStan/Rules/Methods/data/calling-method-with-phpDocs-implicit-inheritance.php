<?php

namespace MethodWithPhpDocsImplicitInheritance;

interface FooInterface
{

	/**
	 * @param string $str
	 */
	public function doBar($str);

}

class Foo implements FooInterface
{

	/**
	 * @param int $i
	 */
	public function doFoo($i)
	{

	}

	public function doBar($str)
	{

	}

}

class Bar extends Foo
{

	public function doFoo($i)
	{

	}

}

class Baz extends Bar
{

	public function doFoo($i)
	{

	}

}

function () {
	$baz = new Baz();
	$baz->doFoo(1);
	$baz->doFoo('1');
	$baz->doBar('1');
	$baz->doBar(1);
};
