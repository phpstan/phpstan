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
