<?php

namespace StaticCallOnExpression;

class Foo
{

	public static function doFoo(): self
	{
		return new static();
	}

}

function () {
	Foo::doFoo()::doBar();
};
