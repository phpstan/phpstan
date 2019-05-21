<?php

namespace Levels\PropertyAccesses;

class Foo
{

	/** @var self */
	public $foo;

	public function doFoo(int $i)
	{
		$foo = $this->foo;
		echo $this->bar;

		$foo = new self();
		$foo = $foo->foo;
		echo $foo->bar;
	}

}

class Bar
{

	/** @var self */
	public static $bar;

	public static function doBar(int $i)
	{
		$bar = Bar::$bar;
		echo Lorem::$bar;

		$bar = new Bar();
		$bar = $bar::$bar;
		echo $bar::$foo;
	}

}

class Baz
{

	/**
	 * @param Foo|Bar $fooOrBar
	 * @param Foo|null $fooOrNull
	 * @param Foo|Bar|null $fooOrBarOrNull
	 * @param Bar|Baz $barOrBaz
	 */
	public function doBaz(
		$fooOrBar,
		?Foo $fooOrNull,
		$fooOrBarOrNull,
		$barOrBaz
	)
	{
		$foo = $fooOrBar->foo;
		$bar =$fooOrBar->bar;

		$foo = $fooOrNull->foo;
		$bar = $fooOrNull->bar;

		$foo = $fooOrBarOrNull->foo;
		$bar = $fooOrBarOrNull->bar;

		$foo = $barOrBaz->foo;
	}

}

class ClassWithMagicMethod
{

	public function doFoo()
	{
		$this->test = 'test';
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set(string $name, $value)
	{

	}

}

class AnotherClassWithMagicMethod
{

	public function doFoo()
	{
		echo $this->test;
	}

	public function __get(string $name)
	{

	}

}

class Ipsum
{

	/**
	 * @return Foo|Bar
	 */
	private function makeFooOrBar()
	{
		if (rand(0, 1) === 0) {
			return new Foo();
		} else {
			return new Bar();
		}
	}

	/**
	 * @return Foo|null
	 */
	private function makeFooOrNull()
	{
		if (rand(0, 1) === 0) {
			return new Foo();
		} else {
			return null;
		}
	}

	/**
	 * @return Foo|Bar|null
	 */
	public function makeFooOrBarOrNull()
	{
		if (rand(0, 1) === 0) {
			return new Foo();
		} elseif (rand(0, 1) === 1) {
			return new Bar();
		} else {
			return null;
		}
	}

	/**
	 * @return Bar|Baz
	 */
	public function makeBarOrBaz()
	{
		if (rand(0, 1) === 0) {
			return new Bar();
		} else {
			return new Baz();
		}
	}

	public function doBaz()
	{
		$fooOrBar = $this->makeFooOrBar();
		$foo = $fooOrBar->foo;
		$bar =$fooOrBar->bar;

		$fooOrNull = $this->makeFooOrNull();
		$foo = $fooOrNull->foo;
		$bar = $fooOrNull->bar;

		$fooOrBarOrNull = $this->makeFooOrBarOrNull();
		$foo = $fooOrBarOrNull->foo;
		$bar = $fooOrBarOrNull->bar;

		$barOrBaz = $this->makeBarOrBaz();
		$foo = $barOrBaz->foo;
	}

}
