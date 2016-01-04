<?php

interface Foo
{

	public function fooMethod();

	public static function fooStaticMethod();

}

abstract class Bar implements Foo
{

}

abstract class Baz extends Bar
{

	public function bazMethod()
	{
		$this->fooMethod();
		$this->barMethod();
		self::fooStaticMethod();
		self::barStaticMethod();
	}

}
