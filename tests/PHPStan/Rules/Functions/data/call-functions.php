<?php

namespace CallFunctions;

class Foo
{

	public function doFoo()
	{
		$string = '';
		$arr=[];
		foreach($arr as $item) {
			$string = preg_replace('~test~','',$string);
			assert(is_string($string));
		}
	}

	public function doBar()
	{
		while (false !== $lines = fgets(STDIN)) {
			$linesArray = preg_split('/\s+/', $lines);
		}
	}

	public function doBaz(callable $a): void
	{
		set_error_handler($a);
	}

}
