<?php

namespace PassedByReference;

function foo(&$foo)
{

}

function () {
	$i = 0;
	foo($i); // ok

	$arr = [1, 2, 3];
	foo($arr[0]); // ok

	foo(rand());
	foo(null);
};
