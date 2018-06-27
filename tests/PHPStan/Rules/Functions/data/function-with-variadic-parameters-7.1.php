<?php

namespace FunctionWithVariadicParameters;

class Foo
{

    /**
     * @param int[] $integers
     * @param string[] $strings
     * @param int[]|\Traversable $traversable
     */
    public function doFoo(iterable $integers, iterable $strings, \Traversable $traversable)
    {
        foo('x', ...$integers);
        foo('x', ...$strings);
        foo('x', ...$traversable);
    }
}
