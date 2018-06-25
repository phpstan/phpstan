<?php

namespace InternalAnnotations;

function foo()
{

}

/**
 * @internal
 */
function internalFoo()
{

}

class Foo
{

	const FOO = 'foo';

	public $foo;

	public static $staticFoo;

	public function foo()
	{

	}

	public static function staticFoo()
	{

	}

}

/**
 * @internal
 */
class InternalFoo
{

	/**
	 * @internal
	 */
	const INTERNAL_FOO = 'internal_foo';

	/**
	 * @internal
	 */
	public $internalFoo;

	/**
	 * @internal
	 */
	public static $internalStaticFoo;

	/**
	 * @internal
	 */
	public function internalFoo()
	{

	}

	/**
	 * @internal
	 */
	public static function internalStaticFoo()
	{

	}

}

interface FooInterface
{

	const FOO = 'foo';

	public function foo();

	public static function staticFoo();

}

/**
 * @internal
 */
interface InternalFooInterface
{

	/**
	 * @internal
	 */
	const INTERNAL_FOO = 'internal_foo';

	/**
	 * @internal
	 */
	public function internalFoo();

	/**
	 * @internal
	 */
	public static function internalStaticFoo();

}

trait FooTrait
{

	public $foo;

	public static $staticFoo;

	public function foo()
	{

	}

	public static function staticFoo()
	{

	}

}

/**
 * @internal
 */
trait InternalFooTrait
{

	/**
	 * @internal
	 */
	public $internalFoo;

	/**
	 * @internal
	 */
	public static $internalStaticFoo;

	/**
	 * @internal
	 */
	public function internalFoo()
	{

	}

	/**
	 * @internal
	 */
	public static function internalStaticFoo()
	{

	}

}
