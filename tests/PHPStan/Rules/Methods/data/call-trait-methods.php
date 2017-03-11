<?php

trait Foo
{
    public function fooMethod()
    {
    }
}

class Bar
{
    use Foo;
}

class Baz extends Bar
{
    public function bazMethod()
    {
        $this->fooMethod();
        $this->unexistentMethod();
    }
}
