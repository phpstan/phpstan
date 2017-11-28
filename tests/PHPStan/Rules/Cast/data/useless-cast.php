<?php

namespace UselessCast;

$foo = (int) '5';
$foo = (int) 5;
$foo = (string) 5;
$foo = (string) '5';
$foo = (object) new \stdClass();

/** @var string|null $nullableString */
$nullableString = 'foo';
$foo = (string) $nullableString;

$foo = (float) (6 / 2);

$width = 1;
$scale = 2.0;
$width *= $scale;
echo (int) $width;

/** @var string|mixed $stringOrMixed */
$stringOrMixed = doFoo();
(string) $stringOrMixed;
