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
};
