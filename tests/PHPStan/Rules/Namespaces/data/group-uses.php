<?php

namespace SomeOtherUseNamespace;

use function Uses\{
	Foo,
	baz
};
use Uses\{
	Bar,
	LOREM,
	Nonexistent, // could be namespace
	function Foo as fooFunctionAgain,
	const MY_CONSTANT,
	const OTHER_CONSTANT
};
use const Uses\{
	MY_CONSTANT as MY_CONSTANT_AGAIN
};
