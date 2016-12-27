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

	/**
	 * @return static[]
	 */
	public function doFluentArray(): array
	{

	}

	/**
	 * @return static[]|static|Foo
	 */
	public function doFluentUnionIterable()
	{

	}

}
