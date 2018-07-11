<?php

namespace AccessInternalPropertyInExternalPath;

trait FooTrait
{

	public $fooFromTrait;

	/**
	 * @internal
	 */
	public $internalFooFromTrait;

}

class Foo {

	use FooTrait;

	public $foo;

	/**
	 * @internal
	 */
	public $internalFoo;

}
