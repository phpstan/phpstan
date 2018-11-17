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

function (
	int $int1,
	int $int2,
	string $str1,
	string $str2
) {
	$int1 << $int2;
	$int1 >> $int2;
	$int1 >>= $int2;

	$str1 << $str2;
	$str1 >> $str2;
	$str1 >>= $str2;
};

function (
	int $int1,
	int $int2,
	string $str1,
	string $str2
) {
	$int1 <<= $int2;
	$str1 <<= $str2;
};

function (
	int $int,
	string $string
) {
	$int & 5;
	$int & "5";
	$string & "x";
	$string & 5;
	$int | 5;
	$int | "5";
	$string | "x";
	$string | 5;
	$int ^ 5;
	$int ^ "5";
	$string ^ "x";
	$string ^ 5;
};

function (
	string $string1,
	string $string2,
	stdClass $std,
	\Test\ClassWithToString $classWithToString
) {
	$string1 . $string2;
	$string1 . $std;
	$string1 . $classWithToString;

	$string1 .= $string2;
	$string1 .= $std;
	$string2 .= $classWithToString;
};

function ()
{
	$result = [
		'id' => 'blabla', // string
		'allowedRoomCounter' => 0,
		'roomCounter' => 0,
	];

	foreach ([1, 2] as $x) {
		$result['allowedRoomCounter'] += $x;
	}
};

function () {
	$o = new stdClass;
	$o->user ?? '';
	$o->user->name ?? '';

	nonexistentFunction() ?? '';
};

function () {
	$possibleZero = 0;
	if (doFoo()) {
		$possibleZero = 1;
	}

	5 / $possibleZero;
};

function ($a, array $b, string $c) {
	echo str_replace('abc', 'def', $a) . 'xyz';
	echo str_replace('abc', 'def', $b) . 'xyz';
	echo str_replace('abc', 'def', $c) . 'xyz';

	$strOrArray = 'str';
	if (rand(0, 1) === 0) {
		$strOrArray = [];
	}
	echo str_replace('abc', 'def', $strOrArray) . 'xyz';

	echo str_replace('abc', 'def', $a) + 1;
};

function (array $a) {
	$b = [];
	if (rand(0, 1)) {
		$b['foo'] = 'bar';
	}
	$b += $a;
};

function (array $a) {
	$b = [];
	if (rand(0, 1)) {
		$b['foo'] = 'bar';
	}
	$a + $b;
};

function (stdClass $ob, int $n) {
    $ob == $n;
    $ob + $n;
};
