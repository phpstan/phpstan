<?php

namespace CallCallables;

class Foo
{

	public function doFoo(
		$mixed,
		callable $callable,
		string $string,
		\Closure $closure
	)
	{
		$mixed();
		$callable();
		$string();
		$closure();

		$date = 'date';
		$date();

		$nonexistent = 'nonexistent';
		$nonexistent();
	}

}
