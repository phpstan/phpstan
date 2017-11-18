<?php

namespace CallStaticMethods;

class Foo
{

	public static function test()
	{
		Bar::protectedMethodFromChild();
	}

	protected static function baz()
	{

	}

	public function loremIpsum()
	{

	}

	private static function dolor()
	{

	}

}

class Bar extends Foo
{

	public static function test()
	{
		Foo::test();
		Foo::baz();
		parent::test();
		parent::baz();
		Foo::bar(); // nonexistent
		self::bar(); // nonexistent
		parent::bar(); // nonexistent
		Foo::loremIpsum(); // instance
		Foo::dolor();
	}

	public function loremIpsum()
	{
		parent::loremIpsum();
	}

	protected static function protectedMethodFromChild()
	{

	}

}

class Ipsum
{

	public static function ipsumTest()
	{
		parent::lorem(); // does not have a parent
		Foo::test();
		Foo::test(1);
		Foo::baz(); // protected and not from a parent
		UnknownStaticMethodClass::loremIpsum();
	}

}

class ClassWithConstructor
{

	private function __construct($foo)
	{

	}

}

class CheckConstructor extends ClassWithConstructor
{

	public function __construct()
	{
		parent::__construct();
	}

}

function () {
	self::someStaticMethod();
	static::someStaticMethod();
	parent::someStaticMethod();
	Foo::test();
	Foo::baz();
	Foo::bar();
	Foo::loremIpsum();
	Foo::dolor();

	\Locale::getDisplayLanguage('cs_CZ'); // OK
	\Locale::getDisplayLanguage('cs_CZ', 'en'); // OK
	\Locale::getDisplayLanguage('cs_CZ', 'en', 'foo'); // should report 3 parameters given, 1-2 required
};

interface SomeInterface
{

}

function (Foo $foo) {
	if ($foo instanceof SomeInterface) {
		$foo::test();
		$foo::test(1, 2, 3);
	}

	/** @var string|int $stringOrInt */
	$stringOrInt = doFoo();
	$stringOrInt::foo();
};

function (FOO $foo)
{
	$foo::test(); // do not report case mismatch

	FOO::unknownMethod();
	FOO::loremIpsum();
	FOO::dolor();
	FOO::test(1, 2, 3);
	FOO::TEST();
	FOO::test();
};

function (string $className) {
	$className::foo();
};
