<?php

namespace ResolveStatic;

class Foo
{

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }
}

class Bar extends Foo
{

}

function () {
    die;
};
