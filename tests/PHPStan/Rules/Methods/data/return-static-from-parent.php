<?php

namespace ReturnStaticFromParent;

class Foo
{

	/**
	 * @return static
	 */
	public function doFoo(): self
	{

	}

}

class Bar extends Foo
{

}

class Baz extends Bar
{

	public function doBaz(): self
	{
		$baz = $this->doFoo();
		return $baz;
	}

}
