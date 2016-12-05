<?php // lint >= 7.1

$array = [1, 2, 3, [4, 5]];
[$a, $b, $c, [$d, $e]] = $array;

foreach ($array as [$destructuredA, $destructuredB, [$destructuredC, $destructuredD]]) {

}
