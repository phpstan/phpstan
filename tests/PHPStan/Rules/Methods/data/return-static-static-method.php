<?php

namespace ReturnStaticStaticMethod;

class Foo
{

    /**
     * @return static
     */
    public static function doFoo(): self
    {
        return new static();
    }
}

class Bar extends Foo
{

    public function doBar()
    {
        self::doFoo()::doFoo()::doBar();
        self::doFoo()::doFoo()::doBaz();
    }
}
