<?php

namespace DeprecatedAnnotations;

function foo()
{

}

/**
 * @deprecated
 */
function deprecatedFoo()
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
 * @deprecated
 */
class DeprecatedFoo
{

	/**
	 * @deprecated
	 */
	const DEPRECATED_FOO = 'deprecated_foo';

	/**
	 * @deprecated
	 */
	public $deprecatedFoo;

	/**
	 * @deprecated
	 */
	public static $deprecatedStaticFoo;

	/**
	 * @deprecated
	 */
	public function deprecatedFoo()
	{

	}

	/**
	 * @deprecated
	 */
	public static function deprecatedStaticFoo()
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
 * @deprecated
 */
interface DeprecatedFooInterface
{

	/**
	 * @deprecated
	 */
	const DEPRECATED_FOO = 'deprecated_foo';

	/**
	 * @deprecated
	 */
	public function deprecatedFoo();

	/**
	 * @deprecated
	 */
	public static function deprecatedStaticFoo();

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
 * @deprecated
 */
trait DeprecatedFooTrait
{

	/**
	 * @deprecated
	 */
	public $deprecatedFoo;

	/**
	 * @deprecated
	 */
	public static $deprecatedStaticFoo;

	/**
	 * @deprecated
	 */
	public function deprecatedFoo()
	{

	}

	/**
	 * @deprecated
	 */
	public static function deprecatedStaticFoo()
	{

	}

}
