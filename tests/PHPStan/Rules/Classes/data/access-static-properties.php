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
};
