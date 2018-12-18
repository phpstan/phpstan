<?php

namespace AliasedTraitsProblem;

trait Foo
{

	public function fooBaz($var = null)
	{

	}

}

trait Baz
{

	use Foo {
		fooBaz as protected fooBazFromFoo;
	}

	public function fooBaz($var, $var2 = null)
	{

	}
}

class Test
{

	use Baz;

	public function test()
	{
		$this->fooBaz('foo', 'baz');
	}
}
