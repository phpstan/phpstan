<?php

namespace ClassConstantNamespace;

class Foo
{

	const LOREM = 1;
	const IPSUM = 2;

	public function fooMethod()
	{
		self::class;
		self::LOREM;
		self::IPSUM;
	}

}

abstract class AbstractFoo
{
	public function AbstractFooMethod()
	{
		static::LOREM;
	}
}

class FooBar extends AbstractFoo
{
	const LOREM = 1;

	public function FooBarMethod()
	{
		static::IPSUM;
	}
}
