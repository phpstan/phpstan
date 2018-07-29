<?php

/** @var int|object $maybeInt */
$maybeInt = null;

$string = 'foo';
$string[0];
$string[0] = 'o';

$string = 'foo';
$string[];
$string[] = 'foo';

$string = 'foo';
$string['foo'];
$string['foo'] = 'foo';

$string = 'foo';
$string[12.34];
$string[12.34] = 'foo';

$string = 'foo';
$string[$maybeInt];
$string[$maybeInt] = 'foo';

/** @var string|array $maybeString */
$maybeString = [];
$maybeString[0];
$maybeString[0] = 'foo';

/** @var string|array $maybeString */
$maybeString = [];
$maybeString['foo'];
$maybeString['foo'] = 'foo';

/** @var string|array $maybeString */
$maybeString = [];
$maybeString[12.34];
$maybeString[12.34] = 'foo';

/** @var string|array $maybeString */
$maybeString = [];
$maybeString[$maybeInt];
$maybeString[$maybeInt] = 'foo';

/** @var mixed $mixed */
$mixed = null;
$mixed[0];
$mixed[0] = 'foo';

/** @var mixed $mixed */
$mixed = null;
$mixed['foo'];
$mixed['foo'] = 'foo';

/** @var mixed $mixed */
$mixed = null;
$mixed[12.34];
$mixed[12.34] = 'foo';

/** @var mixed $mixed */
$mixed = null;
$mixed[$maybeInt];
$mixed[$maybeInt] = 'foo';
