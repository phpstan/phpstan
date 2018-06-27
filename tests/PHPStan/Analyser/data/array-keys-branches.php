<?php

function (array $generalArray) {
    $array = [
        'i' => 0,
        'j' => 0,
        'k' => 0,
        'l' => 0,
        'm' => 0,
    ];

    /** @var \DateTimeImmutable|null $nullableDateTime */
    $nullableDateTime = doFoo();
    $array['key'] = $nullableDateTime;

    $arrayAppendedInIf = ['foo', 'bar'];
    if ($array['key'] === null) {
        $array['key'] = new \DateTimeImmutable();
        $arrayAppendedInIf[] = 'baz';
    }

    if ($generalArray['key'] === null) {
        $generalArray['key'] = new \DateTimeImmutable();
    }

    foreach ([1, 2] as $x) {
        $array['i'] += $x;
        $array['k']++;
    }

    /** @var int[] $ints */
    $ints = doFoo();
    $arrayAppendedInForeach = ['foo', 'bar'];
    $anotherArrayAppendedInForeach = ['foo', 'bar'];
    $i = 0;
    foreach ($ints as $x) {
        $array['j'] += $x;
        $arrayAppendedInForeach[] = 'baz';
        $anotherArrayAppendedInForeach[$i++] = 'baz';
    }

    $array['l']++;
    $array['m'] += 5;

    die;
};
