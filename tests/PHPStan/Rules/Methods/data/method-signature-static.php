<?php

namespace MethodSignature;

class Foo
{

	/**
	 * @param int $value
	 */
	public static function doFoo($value)
	{

	}

}

class Bar extends Foo
{

	/**
	 * @param string $value
	 */
	public static function doFoo($value)
	{

	}

}
