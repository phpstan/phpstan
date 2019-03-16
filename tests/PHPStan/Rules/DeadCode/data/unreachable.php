<?php

namespace DeadCodeUnreachable;

class Foo
{

	public function doFoo()
	{
		if (doFoo()) {
			return;
			return;
		}
	}

	public function doBar(string $foo)
	{
		return;
		echo $foo;
	}

	public function doBaz($foo)
	{
		if ($foo) {
			return;
		} else{
			return;
		}

		echo $foo;
	}

	public function doLorem()
	{
		return;
		// this is why...
	}

}
