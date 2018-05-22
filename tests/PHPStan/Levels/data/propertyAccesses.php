<?php

namespace Levels\PropertyAccesses;

class Foo
{

	/** @var self */
	public $foo;

	public function doFoo(int $i)
	{
		$this->foo;
		$this->bar;

		$foo = new self();
		$foo->foo;
		$foo->bar;
	}

}

class Bar
{

	/** @var self */
	public static $bar;

	public static function doBar(int $i)
	{
		Bar::$bar;
		Lorem::$bar;

		$bar = new Bar();
		$bar::$bar;
		$bar::$foo;
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
		$fooOrBar->foo;
		$fooOrBar->bar;

		$fooOrNull->foo;
		$fooOrNull->bar;

		$fooOrBarOrNull->foo;
		$fooOrBarOrNull->bar;

		$barOrBaz->foo;
	}

}
