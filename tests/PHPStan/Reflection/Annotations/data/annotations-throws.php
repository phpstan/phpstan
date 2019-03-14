<?php

namespace ThrowsAnnotations;

function withoutThrows()
{

}

/**
 * @throws \RuntimeException Function description 1.
 * @throws \RuntimeException Function description 2.
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
	 * @throws \RuntimeException Class instance method description 1.
	 * @throws \RuntimeException Class instance method description 2.
	 */
	public function throwsRuntime()
	{

	}

	/**
	 * @throws \RuntimeException Class static method description.
	 */
	public static function staticThrowsRuntime()
	{

	}

}

interface FooInterface
{

	public function withoutThrows();

	/**
	 * @throws \RuntimeException Interface instance method description.
	 */
	public function throwsRuntime();

	/**
	 * @throws \RuntimeException Interface static method description 1.
	 * @throws \RuntimeException Interface static method description 2.
	 */
	public static function staticThrowsRuntime();

}

trait FooTrait
{

	public function withoutThrows()
	{

	}

	/**
	 * @throws \RuntimeException Trait instance method description 1.
	 * @throws \RuntimeException Trait instance method description 2.
	 */
	public function throwsRuntime()
	{

	}

	/**
	 * @throws \RuntimeException Trait static method description.
	 */
	public static function staticThrowsRuntime()
	{

	}

}
