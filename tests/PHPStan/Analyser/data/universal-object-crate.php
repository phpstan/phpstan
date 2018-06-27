<?php

namespace UniversalObjectCrate;

class Foo extends \stdClass
{

    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function doFoo()
    {
        $this->doBar($this->name);
        $this->doBaz($this->name); // reported - string passed to int
    }

    public function doBar(string $name)
    {
    }

    public function doBaz(int $i)
    {
    }
}

function () {
    $foo = new Foo('foo');
    $foo->doBaz($foo->name); // not reported, is mixed here
};
