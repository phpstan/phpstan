<?php

namespace InheritDocWithoutCurlyBracesFromTrait;

class Foo implements FooInterface
{
	use FooTrait;
}

trait FooTrait
{

	/**
	 * @inheritdoc
	 */
	public function doFoo($string)
	{
		die;
	}

}

interface FooInterface
{

	/**
	 * @param string $string
	 */
	public function doFoo($string);

}
