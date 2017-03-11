<?php

namespace ArrayObjectType;

use AnotherNamespace\Foo;

class Test
{
    const ARRAY_CONSTANT = [0, 1, 2, 3];
    const MIXED_CONSTANT = [0, 'foo'];

    public function doFoo()
    {
        /** @var $foos Foo[] */
        $foos = foos();

        foreach ($foos as $foo) {
            die;
        }
    }
}
