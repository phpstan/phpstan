<?php

namespace ThrowsAnnotations;

function withoutThrows()
{

}

/**
 * @throws \RuntimeException
 */
function throwsRuntime()
{

}

class Foo
{

	public function withoutThrows()
	{

	}

	/**
	 * @throws \RuntimeException
	 */
	public function throwsRuntime()
	{

	}

	/**
	 * @throws \RuntimeException
	 */
	public static function staticThrowsRuntime()
	{

	}

}

interface FooInterface
{

	public function withoutThrows();

	/**
	 * @throws \RuntimeException
	 */
	public function throwsRuntime();

	/**
	 * @throws \RuntimeException
	 */
	public static function staticThrowsRuntime();

}

trait FooTrait
{

	public function withoutThrows()
	{

	}

	/**
	 * @throws \RuntimeException
	 */
	public function throwsRuntime()
	{

	}

	/**
	 * @throws \RuntimeException
	 */
	public static function staticThrowsRuntime()
	{

	}

}
