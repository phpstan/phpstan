<?php

namespace MethodTemplateType;

class Foo
{

	/**
	 * @template stdClass
	 */
	public function doFoo()
	{

	}

	/**
	 * @template T of Zazzzu
	 */
	public function doBar()
	{

	}

}

/**
 * @template T of \Exception
 * @template Z
 */
class Bar
{

	/**
	 * @template T
	 * @template U
	 */
	public function doFoo()
	{

	}

}
