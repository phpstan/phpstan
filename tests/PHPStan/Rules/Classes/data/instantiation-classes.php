<?php

namespace TestInstantiation;

class FooInstantiation
{
}

class BarInstantiation
{
    public function __construct($bar)
    {
    }
}

abstract class LoremInstantiation
{
}

interface IpsumInstantiation
{
}

class ClassWithVariadicConstructor
{
    public function __construct()
    {
        $argsCount = func_num_args();
        for ($i = 0; $i < $argsCount; $i++) {
            $arg = func_get_arg($i);
        }
    }
}
