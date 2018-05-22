<?php

namespace Levels\ConstantAccesses;

function () {
	echo UNKNOWN_CONSTANT;
};

class Foo
{

	public const FOO_CONSTANT = 'foo';

	public function doFoo()
	{
		echo Foo::FOO_CONSTANT;
		echo Foo::BAR_CONSTANT;
		echo Bar::FOO_CONSTANT;

		$this::BAR_CONSTANT;

		$foo = new self();
		$foo::BAR_CONSTANT;
	}

}

class Bar
{

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
		$fooOrBar::FOO_CONSTANT;
		$fooOrBar::BAR_CONSTANT;

		$fooOrNull::FOO_CONSTANT;
		$fooOrNull::BAR_CONSTANT;

		$fooOrBarOrNull::FOO_CONSTANT;
		$fooOrBarOrNull::BAR_CONSTANT;

		$barOrBaz::FOO_CONSTANT;
	}

}
