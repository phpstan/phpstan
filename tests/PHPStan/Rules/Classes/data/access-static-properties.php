<?php

class FooAccessStaticProperties
{

	public static $test;

	protected static $foo;

	public $loremIpsum;

}

class BarAccessStaticProperties extends FooAccessStaticProperties
{

	public static function test()
	{
		FooAccessStaticProperties::$test;
		FooAccessStaticProperties::$foo;
		parent::$test;
		parent::$foo;
		FooAccessStaticProperties::$bar; // nonexistent
		self::$bar; // nonexistent
		parent::$bar; // nonexistent
		FooAccessStaticProperties::$loremIpsum; // instance
		static::$foo;
	}

	public function loremIpsum()
	{
		parent::$loremIpsum;
	}

}

class IpsumAccessStaticProperties
{

	public static function ipsum()
	{
		parent::$lorem; // does not have a parent
		FooAccessStaticProperties::$test;
		FooAccessStaticProperties::$foo; // protected and not from a parent
		FooAccessStaticProperties::$$foo;
	}

}
