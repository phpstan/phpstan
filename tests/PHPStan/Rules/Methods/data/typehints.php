<?php

namespace TestMethodTypehints;

class FooMethodTypehints
{

	function foo(FooMethodTypehints $foo, $bar, array $lorem): NonexistentClass
	{

	}

	function bar(BarMethodTypehints $bar): array
	{

	}

	function baz(...$bar): FooMethodTypehints
	{

	}

}
