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

set_error_handler(function ($errno, $errstr, $errfile, $errline): void {
	// ...
});
