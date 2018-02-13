<?php

/** @var float $float */
$float = doFoo();

/** @var int $integer */
$integer = doFoo();

$string = 'foo';

/** @var string|null $stringOrNull */
$stringOrNull = doFoo();

$arrayOfIntegers = [$integer, $integer + 1, $integer + 2];

$foo = new Foo();

die;
