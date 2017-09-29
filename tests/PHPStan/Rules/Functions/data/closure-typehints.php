<?php

namespace TestClosureFunctionTypehints;

class FooFunctionTypehints
{

}

$callback = function (FooFunctionTypehints $foo, $bar, array $lorem): NonexistentClass
{

};

$callback = function (BarFunctionTypehints $bar): array
{

};

$callback = function (...$bar): FooFunctionTypehints
{

};

$callback = function (): parent
{

};

$callback = function (fOOfUnctionTypehints $foo): FOOfUnctionTypehintS
{

};

$callback = function (\ReturnTypes\FooAliaS $ignoreAlias)
{

};
