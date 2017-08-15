<?php

namespace InvalidKeyArrayDimFetch;

$a = [];
$foo = $a[null];
$foo = $a[new \DateTimeImmutable()];
$a[[]] = $foo;
$a[1];
$a[1.0];
$a['1'];
$a[true];
$a[false];

/** @var string|null $stringOrNull */
$stringOrNull = doFoo();
$a[$stringOrNull];

$obj = new \SplObjectStorage();
$obj[new \stdClass()] = 1;
