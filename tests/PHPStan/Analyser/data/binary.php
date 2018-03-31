<?php

/** @var float $float */
$float = doFoo();

/** @var int $integer */
$integer = doFoo();

/** @var bool $bool */
$bool = doFoo();

$string = 'foo';

/** @var string|null $stringOrNull */
$stringOrNull = doFoo();

$arrayOfIntegers = [$integer, $integer + 1, $integer + 2];

$foo = new Foo();

$one = 1;

$array = [1, 2, 3];

reset($array);

/** @var number $number */
$number = doFoo();

/** @var int|null|bool $otherInteger */
$otherInteger = doFoo();

die;
