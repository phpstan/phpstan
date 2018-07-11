<?php

namespace CheckInternalStaticMethodCallInInternalPath;

class Foo
{

	public static function foo()
	{

	}

	/**
	 * @internal
	 */
	public static function internalFoo()
	{

	}

	/**
	 * @internal
	 */
	public static function internalFoo2()
	{

	}

}

class Bar extends Foo
{

	public static function internalFoo()
	{

	}

}

/**
 * @internal
 */
class InternalBar extends Foo
{

}
