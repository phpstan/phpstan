<?php

namespace UselessCast;

$foo = (int) '5';
$foo = (int) 5;
$foo = (string) 5;
$foo = (string) '5';
$foo = (object) new \stdClass();

/** @var $nullableString string|null */
$nullableString = 'foo';
$foo = (string) $nullableString;

$foo = (float) (6 / 2);
