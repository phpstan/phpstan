<?php

namespace Test;

use FooBar;

class Foo
{

	public function doFoo(): iterable
	{
		yield 1;
		yield 2;
		return;
	}

}

$foo = new Foo();
$foo->doFoo();
