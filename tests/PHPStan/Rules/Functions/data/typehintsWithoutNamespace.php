<?php

class FooFunctionTypehints
{

}

trait SomeTraitWithoutNamespace
{

}

function fooWithoutNamespace(FooFunctionTypehints $foo, $bar, array $lorem): NonexistentClass
{

}

function barWithoutNamespace(BarFunctionTypehints $bar): array
{

}

function bazWithoutNamespace(...$bar): FooFunctionTypehints
{

}

/**
 * @return parent
 */
function returnParentWithoutNamespace()
{

}

function badCaseTypehintsWithoutNamespace(fOOFunctionTypehints $foo): fOOFunctionTypehintS
{

}

/**
 * @param FOOFunctionTypehints $foo
 * @return FOOFunctionTypehints
 */
function badCaseInNativeAndPhpDocWithoutNamespace(FooFunctionTypehints $foo): FooFunctionTypehints
{

}

/**
 * @param FooFunctionTypehints $foo
 * @return FooFunctionTypehints
 */
function anotherBadCaseInNativeAndPhpDocWithoutNamespace(FOOFunctionTypehints $foo): FOOFunctionTypehints
{

}

function referencesTraitsInNativeWithoutNamespace(SomeTraitWithoutNamespace $trait): SomeTraitWithoutNamespace
{

}

/**
 * @param SomeTraitWithoutNamespace $trait
 * @return SomeTraitWithoutNamespace
 */
function referencesTraitsInPhpDocWithoutNamespace($trait)
{

}
