<?php

namespace TraitsWrongProperty;

use Lorem as Bar;

class Foo
{

    use FooTrait;

    public function doFoo()
    {
        $this->id = 1;
        $this->id = 'foo';

        $this->bar = 1;
    }
}
