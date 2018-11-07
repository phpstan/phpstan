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

class FooParentParent
{

	/**
	 * @return void
	 */
	public function phpDocVoidParentMethod()
	{

	}

}

abstract class FooParent extends FooParentParent implements FooInterface
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

	/**
	 * {@inheritDoc}
	 */
	public function phpDocVoidParentMethod()
	{

	}

}
