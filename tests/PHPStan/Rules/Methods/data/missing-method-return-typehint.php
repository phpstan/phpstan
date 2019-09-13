<?php

namespace MissingMethodReturnTypehint;

interface FooInterface
{

    public function getFoo($p1);

}

class FooParent
{

    public function getBar($p2)
    {

    }

}

class Foo extends FooParent implements FooInterface
{

    public function getFoo($p1)
    {

    }

    /**
     * @param $p2
     */
    public function getBar($p2)
    {

    }

    public function getBaz(): bool
    {
        return false;
    }

}
