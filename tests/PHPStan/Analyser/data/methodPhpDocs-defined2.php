<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;

interface FooInterface
{

	/**
	 * @return void
	 */
	public function phpDocVoidMethodFromInterface();

}

abstract class FooParent implements FooInterface
{

	/**
	 * @return Static
	 */
	public function doLorem()
	{

	}

	/**
	 * @return static
	 */
	public function doIpsum(): self
	{

	}

	/**
	 * @return $this
	 */
	public function doThis()
	{
		return $this;
	}

	/**
	 * @return $this|null
	 */
	public function doThisNullable()
	{
		return $this;
	}

	/**
	 * @return $this|Bar|null
	 */
	public function doThisUnion()
	{

	}

	/**
	 * @return void
	 */
	public function phpDocVoidMethod()
	{

	}

}
