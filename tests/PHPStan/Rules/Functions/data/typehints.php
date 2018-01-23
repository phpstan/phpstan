<?php

namespace TestFunctionTypehints;

class FooFunctionTypehints
{

}

trait SomeTrait
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

function badCaseTypehints(fOOFunctionTypehints $foo): fOOFunctionTypehintS
{

}

/**
 * @param FOOFunctionTypehints $foo
 * @return FOOFunctionTypehints
 */
function badCaseInNativeAndPhpDoc(FooFunctionTypehints $foo): FooFunctionTypehints
{

}

/**
 * @param FooFunctionTypehints $foo
 * @return FooFunctionTypehints
 */
function anotherBadCaseInNativeAndPhpDoc(FOOFunctionTypehints $foo): FOOFunctionTypehints
{

}

function referencesTraitsInNative(SomeTrait $trait): SomeTrait
{

}

/**
 * @param SomeTrait $trait
 * @return SomeTrait
 */
function referencesTraitsInPhpDoc($trait)
{

}
