<?php

namespace ThisVariable;

class Foo
{
    public function doFoo()
    {
        $this->test;
        $foo->test;
    }

    public static function doBar()
    {
        $this->test;
        $foo->test;
        $$bar->test;
    }
}

function () {
    $this->foo;
};

new class() {
    public function doFoo()
    {
        $this->foo;
    }

    public static function doBar()
    {
        $this->foo;
    }
};
