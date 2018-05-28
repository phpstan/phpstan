<?php

namespace VariableCloning;

function (Foo $foo, ?Foo $nullableFoo) {
	clone $foo;
	clone $nullableFoo;
	clone null;
};
