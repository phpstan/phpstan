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
$reducedIntegersToStringWithNull = array_reduce($uniquedIntegers, function (): string {

});
$reducedIntegersToStringAnother = array_reduce($integers, function (): string {

}, 'initial');
$reducedToNull = array_reduce([], function (): string {

});
$reducedToInt = array_reduce([], function (): string {

}, 1);
$reducedIntegersToStringWithInt = array_reduce($uniquedIntegers, function (): string {

}, 1);

$reversedIntegers = array_reverse($integers);

$filledIntegers = array_fill(0, 5, 1);
$filledIntegersWithKeys = array_fill_keys([0], 1);

$integerKeys = [
	1 => 'foo',
	2 => new \stdClass(),
];

$stringKeys = [
	'foo' => 'foo',
	'bar' => new \stdClass(),
];

/** @var \stdClass[] $stdClassesWithIsset */
$stdClassesWithIsset = doFoo();
if (rand(0, 1) === 0) {
	$stdClassesWithIsset[] = new \stdClass();
}
if (!isset($stdClassesWithIsset['baz'])) {
	return;
}

$stringOrIntegerKeys = [
	'foo' => new \stdClass(),
	1 => new \stdClass(),
];

$constantArrayWithFalseyValues = [null, '', 1];

$constantTruthyValues = array_filter($constantArrayWithFalseyValues);

/** @var array<int, false|null> $falsey */
$falsey = doFoo();

/** @var array<int, bool|null> $withFalsey */
$withFalsey = doFoo();

$union = ['a' => 1];
if (rand(0, 1) === 1) {
	$union['b'] = false;
}

/** @var bool $bool */
$bool = doFoo();
/** @var int $integer */
$integer = doFoo();

$withPossiblyFalsey = [$bool, $integer, '', 'a' => 0];

/** @var array<string, int> $generalStringKeys */
$generalStringKeys = doFoo();

/** @var array<int, int> $generalIntegerKeys */
$generalIntegerKeys = doFoo();

/** @var array<int, \DateTimeImmutable> $generalDateTimeValues */
$generalDateTimeValues = doFoo();

/** @var int $integer */
$integer = doFoo();

/** @var string $string */
$string = doFoo();

/** @var int[] $generalIntegers */
$generalIntegers = doFoo();

$mappedStringKeys = array_map(function (): \stdClass {

}, $generalStringKeys);

/** @var callable $callable */
$callable = doFoo();

$mappedStringKeysWithUnknownClosureType = array_map($callable, $generalStringKeys);
$mappedWrongArray = array_map(function (): string {

}, 1);
$unknownArray = array_map($callable, 1);

$conditionalArray = ['foo', 'bar'];
$conditionalKeysArray = [
	'foo' => 1,
	'bar' => 1,
];
if (doFoo()) {
	$conditionalArray[] = 'baz';
	$conditionalArray[] = 'lorem';
	$conditionalKeysArray['baz'] = 1;
	$conditionalKeysArray['lorem'] = 1;
}

/** @var int|string $generalIntegerOrString */
$generalIntegerOrString = doFoo();

/** @var array<int, int|string> $generalArrayOfIntegersOrStrings */
$generalArrayOfIntegersOrStrings = doFoo();

/** @var array<int|string, int> $generalIntegerOrStringKeys */
$generalIntegerOrStringKeys = doFoo();

/** @var array<int|string, mixed> $generalIntegerOrStringKeysMixedValues */
$generalIntegerOrStringKeysMixedValues = doFoo();

$clonedConditionalArray = $conditionalArray;
$clonedConditionalArray[(int)$generalIntegerOrString] = $generalIntegerOrString;

die;
