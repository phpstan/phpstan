<?php

namespace InvalidVarTagType;

class Foo
{

	public function doFoo()
	{
		/** @var self $test */
		$test = new self();

		/** @var self&\stdClass $test */
		$test = new self();

		/** @var self&\stdClass */
		$test = new self();

		/** @var aray $test */
		$test = new self();

		/** @var int&string $value */
		foreach ([1, 2, 3] as $value) {

		}

		/** @var self&\stdClass $staticVar */
		static $staticVar = 1;

		/** @var foo $test */
		$test = new self();

		/** @var FooTrait $test */
		$test = new self();

		/** @var never $test */
		$test = doFoo();

		/** @var \InvalidPhpDoc\Foo<\stdClass> $test */
		$test = doFoo();

		/** @var \InvalidPhpDocDefinitions\FooGeneric<int, \InvalidArgumentException> $test */
		$test = doFoo();

		/** @var \InvalidPhpDocDefinitions\FooGeneric<int> $test */
		$test = doFoo();

		/** @var \InvalidPhpDocDefinitions\FooGeneric<int, \InvalidArgumentException, string> $test */
		$test = doFoo();

		/** @var \InvalidPhpDocDefinitions\FooGeneric<int, \Throwable> $test */
		$test = doFoo();

		/** @var \InvalidPhpDocDefinitions\FooGeneric<int, \stdClass> $test */
		$test = doFoo();
	}

}

trait FooTrait
{

}
