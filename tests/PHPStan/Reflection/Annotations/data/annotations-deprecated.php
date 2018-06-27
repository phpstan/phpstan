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

    public function staticFoo()
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
    public function deprecatedStaticFoo()
    {
    }
}

class FooInterface
{

    const FOO = 'foo';

    public $foo;

    public static $staticFoo;

    public function foo()
    {
    }

    public function staticFoo()
    {
    }
}

/**
 * @deprecated
 */
class DeprecatedFooInterface
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
    public function deprecatedStaticFoo()
    {
    }
}

trait FooTrait
{

    public $foo;

    public static $staticFoo;

    public function foo()
    {
    }

    public function staticFoo()
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
    public function deprecatedStaticFoo()
    {
    }
}
