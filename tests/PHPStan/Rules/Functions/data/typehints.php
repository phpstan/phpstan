<?php

namespace TestFunctionTypehints;

class FooFunctionTypehints
{
}

function foo(FooFunctionTypehints $foo, $bar, array $lorem): NonexistentClass
{
}

function bar(BarFunctionTypehints $bar): array
{
}

function baz(...$bar): FooFunctionTypehints
{
}

/**
 * @return parent
 */
function returnParent()
{
}
