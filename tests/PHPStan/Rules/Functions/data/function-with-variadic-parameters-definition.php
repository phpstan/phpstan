<?php

namespace FunctionWithVariadicParameters;

function foo($bar, ...$foo)
{

}

function bar($foo)
{
	$bar = func_get_args();
}
