<?php

$integers = [1, 2, 3];
$mappedStrings = array_map(function (): string {

}, $integers);

$filteredIntegers = array_filter($integers, function (): bool {

});

$uniquedIntegers = array_unique($integers);

$reducedIntegersToString = array_reduce($integers, function (): string {

});

$reversedIntegers = array_reverse($integers);

die;
