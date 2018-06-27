<?php

namespace FunctionWithVariadicParameters;

function foo($bar, int ...$foo)
{
}

function bar($foo)
{
    $bar = func_get_args();
}
