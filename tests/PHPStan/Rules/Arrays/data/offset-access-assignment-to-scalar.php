<?php

$value = [];
$value['foo'] = null;

$value = 'Foo';
$value['foo'] = null;

$value = 'Foo';
$value[] = 'test';

$value = 'Foo';
$value[12.34] = 'test';

/** @var string|array $maybeString */
$maybeString = [];
$maybeString[0] = 'foo';

/** @var string|array $maybeString */
$maybeString = [];
$maybeString['foo'] = 'foo';

/** @var int|object $maybeInt */
$maybeInt = null;

/** @var string|array $maybeString */
$maybeString = [];
$maybeString[$maybeInt] = 'foo';

$value = 'Foo';
$value[$maybeInt] = 'foo';

$value = new \stdClass();
$value['foo'] = null;

$value = true;
$value['foo'] = null;

$value = false;
$value['foo'] = null;

/** @var resource $value */
$value = null;
$value['foo'] = null;

$value = 42;
$value['foo'] = null;

$value = 4.141;
$value['foo'] = null;

/** @var array|int $value */
$value = [];
$value['foo'] = null;
