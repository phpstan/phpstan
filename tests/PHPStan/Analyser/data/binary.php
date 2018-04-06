<?php

/** @var float $float */
$float = doFoo();

/** @var int $integer */
$integer = doFoo();

/** @var bool $bool */
$bool = doFoo();

/** @var string $string */
$string = doFoo();

$fooString = 'foo';

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

/** @var mixed $mixed */
$mixed = doFoo();

/** @var int[] $arrayOfUnknownIntegers */
$arrayOfUnknownIntegers = doFoo();

$foobarString = $fooString;
$foobarString[6] = 'b';
$foobarString[7] = 'a';
$foobarString[8] = 'r';

die;
