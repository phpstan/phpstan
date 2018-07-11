<?php

namespace AccessInternalStaticPropertyInExternalPath;

trait FooTrait {

	public static $fooFromTrait;

	/**
	 * @internal
	 */
	public static $internalFooFromTrait;

}

class Foo {

	use FooTrait;

	public static $foo;

	/**
	 * @internal
	 */
	public static $internalFoo;

}
