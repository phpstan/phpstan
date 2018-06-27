<?php

namespace InvalidKeyArrayItem;

/** @var string|\stdClass $stringOrObject */
$stringOrObject = doFoo();

$a = [
    'foo',
    1 => 'aaa',
    '1' => 'aaa',
    null => 'aaa',
    new \DateTimeImmutable() => 'aaa',
    [] => 'bbb',
    $stringOrObject => 'aaa',
];
