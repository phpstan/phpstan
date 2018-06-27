<?php

namespace EarlyTermination;

class Foo
{
    public static function doBar()
    {
        throw new \Exception();
    }

    public function doFoo()
    {
        throw new \Exception();
    }
}

class Bar extends Foo
{

}
