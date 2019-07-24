<?php

namespace InheritDocWithoutCurlyBracesFromInterface;

class Foo extends FooParent implements FooInterface
{

	/**
	 * @inheritdoc
	 */
	public function doFoo($string)
	{
		die;
	}

}

abstract class FooParent
{

}

interface FooInterface
{

	/**
	 * @param string $string
	 */
	public function doFoo($string);

}
