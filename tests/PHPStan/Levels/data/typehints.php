<?php

namespace Levels\Typehints;

class Foo
{

	public function doFoo(Lorem $lorem): Ipsum
	{
		return new Ipsum();
	}

	/**
	 * @param Lorem $lorem
	 * @return Ipsum
	 */
	public function doBar($lorem)
	{
		return new Ipsum();
	}

}
