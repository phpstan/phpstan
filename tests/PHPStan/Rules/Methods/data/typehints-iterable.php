<?php // lint >= 7.1

namespace TestMethodTypehints;

class IterableTypehints
{

	/**
	 * @param iterable<NonexistentClass, AnotherNonexistentClass> $iterable
	 */
	public function doFoo(iterable $iterable)
	{

	}

}
