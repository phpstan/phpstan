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
	}

}

trait FooTrait
{

}
