<?php

namespace TraitAliases;

trait BazTrait
{

	public function fooMethod()
	{

	}

}

trait BarTrait
{

	use BazTrait {
		fooMethod as parentFooMethod;
	}

	public function fooMethod()
	{
		// some code ...
		$this->fooMethod();
		$this->parentFooMethod();
	}

}

class Foo
{

	use BarTrait;

	public function doFoo()
	{
		$this->fooMethod();
		$this->parentFooMethod();
	}

}
