<?php

namespace DeadCodeNoop;

function (stdClass $foo) {
	$foo->foo();

	$arr = [];
	$arr;
	$arr['test'];
	$foo::$test;
	$foo->test;

	'foo';
	1;

	@'foo';
	+1;
	-1;

	+$foo->foo();
	-$foo->foo();
	@$foo->foo();

	isset($test);
	empty($test);
	true;
	Foo::TEST;

	(string) 1;
};
