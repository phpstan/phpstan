<?php

$integers = [1, 2, 3];
$mixedValues = ['abc', 123];

$mappedStrings = array_map(function (): string {

}, $integers);

$filteredIntegers = array_filter($integers, function (): bool {

});

$filteredMixed = array_filter($mixedValues, function ($mixedValue): bool {
	return is_int($mixedValue);
});

$uniquedIntegers = array_unique($integers);

$reducedIntegersToString = array_reduce($integers, function (): string {

});

$reversedIntegers = array_reverse($integers);

$filledIntegers = array_fill(0, 5, 1);
$filledIntegersWithKeys = array_fill_keys([], 1);

$integerKeys = [
	1 => 'foo',
	2 => new \stdClass(),
];

$stringKeys = [
	'foo' => 'foo',
	'bar' => new \stdClass(),
];

$stringOrIntegerKeys = [
	'foo' => new \stdClass(),
	1 => new \stdClass(),
];

die;
