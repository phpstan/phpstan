<?php

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
