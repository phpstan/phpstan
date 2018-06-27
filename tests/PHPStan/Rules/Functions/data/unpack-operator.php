<?php

namespace UnpackOperator;

class Foo
{

    /**
     * @param string[] $strings
     */
    public function doFoo(
        array $strings
    ) {
        $constantArray = ['foo', 'bar', 'baz'];
        sprintf('%s', ...$strings);
        sprintf('%s', ...$constantArray);
        sprintf('%s', $strings);
        sprintf('%s', $constantArray);
        sprintf(...$strings);
        sprintf(...$constantArray);
    }
}
