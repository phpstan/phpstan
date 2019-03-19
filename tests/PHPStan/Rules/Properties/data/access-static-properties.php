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
		$class::$property;
		UnknownStaticProperties::$test;

		if (isset(static::$baz)) {
			static::$baz;
		}
		isset(static::$baz);
		static::$baz;
		if (!isset(static::$nonexistent)) {
			static::$nonexistent;
			return;
		}
		static::$nonexistent;

		if (!empty(static::$emptyBaz)) {
			static::$emptyBaz;
		}
		static::$emptyBaz;
		if (empty(static::$emptyNonexistent)) {
			static::$emptyNonexistent;
			return;
		}
		static::$emptyNonexistent;

		isset(static::$anotherNonexistent) ? static::$anotherNonexistent : null;
		isset(static::$anotherNonexistent) ? null : static::$anotherNonexistent;
		!isset(static::$anotherNonexistent) ? static::$anotherNonexistent : null;
		!isset(static::$anotherNonexistent) ? null : static::$anotherNonexistent;

		empty(static::$anotherEmptyNonexistent) ? static::$anotherEmptyNonexistent : null;
		empty(static::$anotherEmptyNonexistent) ? null : static::$anotherEmptyNonexistent;
		!empty(static::$anotherEmptyNonexistent) ? static::$anotherEmptyNonexistent : null;
		!empty(static::$anotherEmptyNonexistent) ? null : static::$anotherEmptyNonexistent;
	}

}

function () {
	self::$staticFooProperty;
	static::$staticFooProperty;
	parent::$staticFooProperty;

	FooAccessStaticProperties::$test;
	FooAccessStaticProperties::$foo;
	FooAccessStaticProperties::$loremIpsum;

	$foo = new FooAccessStaticProperties();
	$foo::$test;
	$foo::$nonexistent;

	$bar = new NonexistentClass();
	$bar::$test;
};

interface SomeInterface
{

}

function (FooAccessStaticProperties $foo) {
	if ($foo instanceof SomeInterface) {
		$foo::$test;
		$foo::$nonexistent;
	}

	/** @var string|int $stringOrInt */
	$stringOrInt = doFoo();
	$stringOrInt::$foo;
};

function (FOOAccessStaticPropertieS $foo) {
	$foo::$test; // do not report case mismatch

	FOOAccessStaticPropertieS::$unknownProperties;
	FOOAccessStaticPropertieS::$loremIpsum;
	FOOAccessStaticPropertieS::$foo;
	FOOAccessStaticPropertieS::$test;
};

function (string $className) {
	$className::$fooProperty;
};

class ClassOrString
{

	private static $accessedProperty;

	private $instanceProperty;

	public function doFoo()
	{
		/** @var self|string $class */
		$class = doFoo();
		$class::$accessedProperty;
		$class::$unknownProperty;

		Self::$accessedProperty;
	}

	public function doBar()
	{
		/** @var self|false $class */
		$class = doFoo();
		if (isset($class::$anotherProperty)) {
			echo $class::$anotherProperty;
			echo $class::$instanceProperty;
		}
	}

}

class AccessPropertyWithDimFetch
{

	public function doFoo()
	{
		self::$foo['foo'] = 'test';
	}

	public function doBar()
	{
		self::$foo = 'test'; // reported by a separate rule
	}

}

class AccessInIsset
{

	public function doFoo()
	{
		if (isset(self::$foo)) {

		}
	}

	public function doBar()
	{
		if (isset(self::$foo['foo'])) {

		}
	}

}
