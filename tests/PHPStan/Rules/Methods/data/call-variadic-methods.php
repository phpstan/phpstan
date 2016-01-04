<?php

namespace CallVariadicMethods;

class Foo
{

	public function bar()
	{
		$this->baz();
		$this->lorem();
		$this->baz(1, 2, 3);
		$this->lorem(1, 2, 3);
	}

	public function baz($foo, ...$bar)
	{

	}

	public function lorem($foo, $bar)
	{
		$foo = 'bar';
		if ($foo) {
			func_get_args();
		}
	}

}
