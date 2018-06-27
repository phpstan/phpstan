<?php

if (!is_int($integer)) {
    throw new \Exception();
}

if (!is_integer($anotherInteger)) {
    throw new \Exception();
}

if (!is_long($longInteger)) {
    throw new \Exception();
}

if (!is_float($float)) {
    throw new \Exception();
}

if (!is_double($doubleFloat)) {
    throw new \Exception();
}

if (!is_real($realFloat)) {
    throw new \Exception();
}

if (!is_null($null)) {
    throw new \Exception();
}

if (!is_array($array)) {
    throw new \Exception();
}

if (!is_bool($bool)) {
    throw new \Exception();
}

if (!is_callable($callable)) {
    throw new \Exception();
}

if (!is_resource($resource)) {
    throw new \Exception();
}

if (!is_string($string)) {
    throw new \Exception();
}

if (!is_object($object)) {
    throw new \Exception();
}

if (!is_int($mixedInteger) && !ctype_digit($whatever)) {
    return;
}

/** @var int|\stdClass $intOrStdClass */
$intOrStdClass = doFoo();
if (!is_numeric($intOrStdClass)) {
    return;
}

$foo = doFoo();
if (!is_a($foo, 'Foo')) {
    return;
}

$anotherFoo = doFoo();
if (!is_a($anotherFoo, Foo::class)) {
    return;
}

assert(is_int($yetAnotherInteger));

$subClassOfFoo = doFoo();
if (!is_subclass_of($subClassOfFoo, Foo::class)) {
    return;
}

$subClassAsString = 'str';
if (!is_subclass_of($subClassAsString, Foo::class)) {
    return;
}

function (Foo $foo) {
    if (!is_subclass_of($foo, '')) {
    }
};

die;
