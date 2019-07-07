<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

trait RecursiveFooTrait
{

	use FooTrait;

}

class FooWithRecursiveTrait extends FooParent
{

	use RecursiveFooTrait;

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

	public function phpDocWithoutCurlyBracesVoidParentMethod(): self
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
