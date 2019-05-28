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
 * @deprecated in 1.0.0.
 */
class DeprecatedFoo
{

	/**
	 * @deprecated Deprecated constant.
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
	 * @deprecated method.
	 */
	public function deprecatedFoo()
	{

	}

	/**
	 * @deprecated static method.
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
 * @deprecated Deprecated interface.
 */
interface DeprecatedFooInterface
{

	/**
	 * @deprecated Deprecated constant.
	 */
	const DEPRECATED_FOO = 'deprecated_foo';

	/**
	 * @deprecated Deprecated method.
	 */
	public function deprecatedFoo();

	/**
	 * @deprecated Deprecated static method.
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
 * @deprecated Deprecated trait.
 */
trait DeprecatedFooTrait
{

	/**
	 * @deprecated Deprecated trait property.
	 */
	public $deprecatedFoo;

	/**
	 * @deprecated Deprecated static trait property.
	 */
	public static $deprecatedStaticFoo;

	/**
	 * @deprecated Deprecated trait method.
	 */
	public function deprecatedFoo()
	{

	}

	/**
	 * @deprecated Deprecated static trait method.
	 */
	public static function deprecatedStaticFoo()
	{

	}

}

/**
 * Class with multiple tags and children.
 *
 * @deprecated in Foo 1.1.0 and will be removed in 1.5.0, use
 *   \Foo\Bar\NotDeprecated instead.
 *
 * @author Foo Baz <foo@baz.com>
 */
class DeprecatedWithMultipleTags
{

	/**
	 * Method documentation.
	 *
	 * @return string
	 *   Returns a string
	 *
	 * @deprecated in Foo 1.1.0, will be removed in Foo 1.5.0, use
	 *   \Foo\Bar\NotDeprecated::replacementFoo() instead.
	 */
	public function deprecatedFoo() {

	}

}
