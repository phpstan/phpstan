<?php

namespace MethodWithInheritDoc;

class Foo
{

	/**
	 * @param int $i
	 */
	public function doFoo($i)
	{

	}

}

class Bar extends Foo
{

	/**
	 * {@inheritDoc}
	 */
	public function doFoo($i)
	{

	}

}

class Baz extends Bar
{

	/**
	 * {@inheritDoc}
	 */
	public function doFoo($i)
	{

	}

}

function () {
	$baz = new Baz();
	$baz->doFoo(1);
	$baz->doFoo('1');
};
