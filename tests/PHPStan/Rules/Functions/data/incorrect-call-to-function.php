<?php

namespace IncorrectCallToFunction;

foo(1);

foo(1, 2, 3);

$array = [
	'foo' => 'bar',
	'bar' => new \stdClass(),
];
$keys = array_keys($array);
bar($keys[0]);
