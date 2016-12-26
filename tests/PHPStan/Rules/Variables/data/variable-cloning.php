<?php

namespace VariableCloning;

class Foo {};

$f = function () {
	$foo = new Foo();
	clone $foo;
	clone new Foo();
	clone (random_int(0, 1) ? 'foo' : 123); // mixed value

	$stringData = 'abc';
	clone $stringData;
	clone 'abc';
};
