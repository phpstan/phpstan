<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;

class Bar
{

	/**
	 * @return self
	 */
	public function doBar()
	{

	}

	/**
	 * @return static
	 */
	public function doFluent()
	{

	}

	/**
	 * @return static|null
	 */
	public function doFluentNullable()
	{

	}

}

class Baz extends Bar
{

}

class FooParent
{

	/**
	 * @return static
	 */
	public function doLorem()
	{

	}

}
