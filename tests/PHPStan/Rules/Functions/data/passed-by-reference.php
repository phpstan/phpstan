<?php

namespace PassedByReference;

function foo(&$foo)
{

}

class Bar
{

	private $barProperty;

	private static $staticBarProperty;

	public function doBar()
	{
		foo($this->barProperty); // ok
		foo(self::$staticBarProperty); // ok
	}

}

function () {
	$i = 0;
	foo($i); // ok

	$arr = [1, 2, 3];
	foo($arr[0]); // ok

	foo(rand());
	foo(null);
};
