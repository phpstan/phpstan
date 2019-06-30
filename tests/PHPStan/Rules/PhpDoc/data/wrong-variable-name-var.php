<?php

namespace WrongVariableNameVarTag;

class Foo
{

	public function doFoo()
	{
		/** @var int $test */
		$test = doFoo();

		/** @var int */
		$test = doFoo();

		/** @var int $foo */
		$test = doFoo();

		/**
		 * @var int
		 * @var string
		 */
		$test = doFoo();
	}

	public function doBar(array $list)
	{
		/** @var int[] $list */
		foreach ($list as $key => $var) { // ERROR

		}

		/** @var int $key */
		foreach ($list as $key => $var) {

		}

		/** @var int $var */
		foreach ($list as $key => $var) {

		}

		/**
		 * @var int $foo
		 * @var int $bar
		 * @var int $baz
		 * @var int $lorem
		 */
		foreach ($list as $key => [$foo, $bar, [$baz, $lorem]]) {

		}

		/**
		 * @var int $foo
		 * @var int $bar
		 * @var int $baz
		 * @var int $lorem
		 */
		foreach ($list as $key => list($foo, $bar, list($baz, $lorem))) {

		}

		/**
		 * @var int $foo
		 */
		foreach ($list as $key => $val) {

		}

		/** @var int */
		foreach ($list as $key => $val) {

		}
	}

	public function doBaz()
	{
		/** @var int $var */
		static $var;

		/** @var int */
		static $var;

		/** @var int */
		static $var, $bar;

		/**
		 * @var int
		 * @var string
		 */
		static $var, $bar;

		/** @var int $foo */
		static $test;
	}

	public function doLorem($test)
	{
		/** @var int $test */
		$test2 = doFoo();

		/** @var int */
		$test->foo();

		/** @var int $test */
		$test->foo();

		/** @var int $foo */
		$test->foo();
	}

}
