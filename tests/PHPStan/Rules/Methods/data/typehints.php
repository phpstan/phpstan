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

	/**
	 * @param FooMethodTypehints[] $foos
	 * @param BarMethodTypehints[] $bars
	 * @return BazMethodTypehints[]
	 */
	function lorem($foos, $bars)
	{

	}

	/**
	 * @param FooMethodTypehints[] $foos
	 * @param BarMethodTypehints[] $bars
	 * @return BazMethodTypehints[]
	 */
	function ipsum(array $foos, array $bars): array
	{

	}

	/**
	 * @param FooMethodTypehints[] $foos
	 * @param FooMethodTypehints|BarMethodTypehints[] $bars
	 * @return self|BazMethodTypehints[]
	 */
	function dolor(array $foos, array $bars): array
	{

	}

	function parentWithoutParent(parent $parent): parent
	{

	}

	/**
	 * @param parent $parent
	 * @return parent
	 */
	function phpDocParentWithoutParent($parent)
	{

	}

}
