<?php

namespace MissingMethodParameterTypehint;

interface FooInterface
{

    public function getFoo($p1): void;

}

class FooParent
{

    public function getBar($p2)
    {

    }

}

class Foo extends FooParent implements FooInterface
{

    public function getFoo($p1): void
    {

    }

    /**
     * @param $p2
     */
    public function getBar($p2)
    {

    }

    /**
     * @param $p3
     * @param int $p4
     */
    public function getBaz($p3, $p4): bool
    {
        return false;
    }

    /**
     * @param mixed $p5
     */
    public function getFooBar($p5): bool
    {
        return false;
    }

}
