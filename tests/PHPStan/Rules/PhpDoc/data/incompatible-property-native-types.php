<?php // lint >= 7.4

namespace IncompatiblePhpDocPropertyNativeType;

class Foo
{

	/** @var self */
	private object $selfOne;

	/** @var object */
	private self $selfTwo;

	/** @var Bar */
	private Foo $foo;

	/** @var string */
	private string $string;

	/** @var string|int */
	private string $stringOrInt;

	/** @var string */
	private ?string $stringOrNull;

}

class Bar
{

}

class Baz
{

	private string $stringProp;

}
