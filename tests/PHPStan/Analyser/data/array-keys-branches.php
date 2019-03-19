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

	$incremented = 0;
	$setFromZeroToOne = 0;
	foreach ($ints as $x) {
		$array['j'] += $x;
		$arrayAppendedInForeach[] = 'baz';
		$anotherArrayAppendedInForeach[$i++] = 'baz';
		$incremented++;
		$setFromZeroToOne = 1;
	}

	$array['l']++;
	$array['m'] += 5;

	if (rand(0, 1) === 1) {
		$array['n'] = 'str';
	}

	die;
};
