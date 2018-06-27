<?php

namespace MethodWithInheritDoc;

interface FooInterface
{

    /**
     * @param string $str
     */
    public function doBar($str);
}

class Foo implements FooInterface
{

    /**
     * @param int $i
     */
    public function doFoo($i)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function doBar($str)
    {
    }
}

class Bar extends Foo
{

    /**
     * {@inheritDoc}
     */
    public function doFoo($i)
    {
    }
}

class Baz extends Bar
{

    /**
     * {@inheritDoc}
     */
    public function doFoo($i)
    {
    }
}

function () {
    $baz = new Baz();
    $baz->doFoo(1);
    $baz->doFoo('1');
    $baz->doBar('1');
    $baz->doBar(1);
};
