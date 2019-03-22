<?php

namespace Levels\MissingReturn;

class Foo
{

	public function doFoo(): int
	{

	}

	/**
	 * @return int
	 */
	public function doBar()
	{

	}

	/**
	 * @return mixed
	 */
	public function doBaz()
	{

	}

}
