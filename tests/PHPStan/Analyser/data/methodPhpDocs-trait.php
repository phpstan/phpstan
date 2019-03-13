<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

class FooWithTrait extends FooParent
{

	use FooTrait;

	/**
	 * @return Bar
	 */
	public static function doSomethingStatic()
	{

	}

	/**
	 * @return self[]
	 */
	public function doBar(): array
	{

	}

	/**
	 * @return self[]
	 */
	public function doAnotherBar(): array
	{

	}

	public function returnParent(): parent
	{

	}

	/**
	 * @return parent
	 */
	public function returnPhpDocParent()
	{

	}

	/**
	 * @return NULL[]
	 */
	public function returnNulls(): array
	{

	}

	public function returnObject(): object
	{

	}

	public function phpDocVoidMethod(): self
	{

	}

	public function phpDocVoidMethodFromInterface(): self
	{

	}

	public function phpDocVoidParentMethod(): self
	{

	}

	/**
	 * @return string[]
	 */
	public function returnsStringArray(): array
	{

	}

	private function privateMethodWithPhpDoc()
	{

	}

}
