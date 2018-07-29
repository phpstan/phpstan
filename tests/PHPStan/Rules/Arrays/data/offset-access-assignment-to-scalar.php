<?php

$value = [];
$value['foo'] = null;

$value = [];
$value['foo'] += null;

$value = 'Foo';
$value['foo'] = null;

$value = 'Foo';
$value['foo'] += null;

$value = new \stdClass();
$value['foo'] = null;

$value = new \stdClass();
$value['foo'] += null;

$value = true;
$value['foo'] = null;

$value = true;
$value['foo'] += null;

$value = false;
$value['foo'] = null;

$value = false;
$value['foo'] += null;

/** @var resource $value */
$value = null;
$value['foo'] = null;

/** @var resource $value */
$value = null;
$value['foo'] += null;

$value = 42;
$value['foo'] = null;

$value = 42;
$value['foo'] += null;

$value = 4.141;
$value['foo'] = null;

$value = 4.141;
$value['foo'] += null;

/** @var array|int $value */
$value = [];
$value['foo'] = null;

/** @var array|int $value */
$value = [];
$value['foo'] += null;
