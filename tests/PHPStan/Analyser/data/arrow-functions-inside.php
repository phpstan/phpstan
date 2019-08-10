<?php // lint >= 7.4

namespace ArrowFunctionsInside;

class Foo
{

	public function doFoo(int $i)
	{
		fn(string $s) => die;
	}

}
