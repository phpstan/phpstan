<?php

function (
	$mixed,
	array $array,
	string $string,
	int $int
) {
	$array += [];

	$array + $array;
	$array - $array;

	5 / 2;
	5 / 0;
	5 % (1 - 1);
	$int / 0.0;

	$mixed + [];
	1 + $string;
	1 + "blabla";
	1 + "123";
};

function (
	array $array
) {
	$array += "foo";
};

function (
	array $array
) {
	$array -= $array;
};
