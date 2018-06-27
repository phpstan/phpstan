<?php

namespace MisleadingTypes;

class Foo
{

    public function misleadingBoolReturnType(): boolean
    {
    }

    public function misleadingIntReturnType(): integer
    {
    }

    public function misleadingMixedReturnType(): mixed
    {
    }
}

function () {
    $foo = new Foo();
    die;
};
