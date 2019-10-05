<?php

namespace PHPStan\Generics\GenericClassStringType;

use function PHPStan\Analyser\assertType;

function (int $i) {
	if ($i < 3) {
		assertType('int<min, 2>', $i);

		$i++;
		assertType('int<min, 3>', $i);
	} else {
		assertType('int<3, max>', $i);
	}

	if ($i < 3) {
		assertType('int<min, 2>', $i);

		$i--;
		assertType('int<min, 1>', $i);
	}

	if ($i < 3 && $i > 5) {
		assertType('*NEVER*', $i);
	} else {
		assertType('int', $i);
	}

	if ($i > 3 && $i < 5) {
		assertType('4', $i);

	} else {
		assertType('int', $i);
	}

	if ($i >= 3 && $i <= 5) {
		assertType('int<3, 5>', $i);

		if ($i === 2) {
			assertType('*NEVER*', $i);
		} else {
			assertType('int<3, 5>', $i);
		}

		if ($i !== 3) {
			assertType('int<4, 5>', $i);
		} else {
			assertType('3', $i);
		}
	}
};


function () {
	for ($i = 0; $i < 5; $i++) {
		assertType('int<min, 4>', $i); // should improved to be int<0, 4>
	}

	$i = 0;
	while ($i < 5) {
		assertType('int<min, 4>', $i); // should improved to be int<0, 4>
		$i++;
	}

	$i = 0;
	while ($i++ < 5) {
		assertType('int<min, 5>', $i); // should improved to be int<0, 4>
	}

	$i = 0;
	while (++$i < 5) {
		assertType('int<min, 4>', $i); // should improved to be int<1, 4>
	}

	$i = 5;
	while ($i-- > 0) {
		assertType('int<0, max>', $i); // should improved to be int<0, 4>
	}

	$i = 5;
	while (--$i > 0) {
		assertType('int<1, max>', $i); // should improved to be int<1, 4>
	}
};


function (int $j) {
	$i = 1;

	assertType('true', $i > 0);
	assertType('true', $i >= 1);
	assertType('true', $i <= 1);
	assertType('true', $i < 2);

	assertType('false', $i < 1);
	assertType('false', $i <= 0);
	assertType('false', $i >= 2);
	assertType('false', $i > 1);

	assertType('true', 0 < $i);
	assertType('true', 1 <= $i);
	assertType('true', 1 >= $i);
	assertType('true', 2 > $i);

	assertType('bool', $j > 0);
	assertType('bool', $j >= 0);
	assertType('bool', $j <= 0);
	assertType('bool', $j < 0);

	if ($j < 5) {
		assertType('bool', $j > 0);
		assertType('false', $j > 4);
		assertType('bool', 0 < $j);
		assertType('false', 4 < $j);

		assertType('bool', $j >= 0);
		assertType('false', $j >= 5);
		assertType('bool', 0 <= $j);
		assertType('false', 5 <= $j);

		assertType('true', $j <= 4);
		assertType('bool', $j <= 3);
		assertType('true', 4 >= $j);
		assertType('bool', 3 >= $j);

		assertType('true', $j < 5);
		assertType('bool', $j < 4);
		assertType('true', 5 > $j);
		assertType('bool', 4 > $j);
	}
};
