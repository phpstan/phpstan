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

/** @var string|\stdClass $stringOrObject */
$stringOrObject = doFoo();
$a[$stringOrObject];

$constantArray = ['a' => 1];
if (doFoo()) {
    $constantArray['b'] = 2;
}

$constantArray[new \DateTimeImmutable()] = 1;

/** @var string[] $array */
$array = doFoo();
foreach ($array as $i => $val) {
    echo $array[$i];
}
