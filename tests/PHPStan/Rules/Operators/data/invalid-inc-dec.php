<?php

namespace InvalidIncDec;

function ($a, int $i, ?float $j, string $str, \stdClass $std) {
    $a++;

    $b = [1];
    $b[0]++;

    date('j. n. Y')++;
    date('j. n. Y')--;

    $i++;
    $j++;
    $str++;
    $std++;
};
