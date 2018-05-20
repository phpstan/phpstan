<?php

namespace UselessCast;

function () {
	$foo = (int) '5';
	$foo = (int) 5;
	$foo = (string) 5;
	$foo = (string) '5';
	$foo = (object) new \stdClass();

	/** @var string|null $nullableString */
	$nullableString = 'foo';
	$foo = (string) $nullableString;

	$foo = (float) (6 / 2);

	$width = 1;
	$scale = 2.0;
	$width *= $scale;
	echo (int) $width;

	/** @var string|mixed $stringOrMixed */
	$stringOrMixed = doFoo();
	(string) $stringOrMixed;

	$foo = (float) (100.0 / 25.432);

	(int) "blabla";
};

function foo(string &$input) {
	$input = 1;
}

function () {
	$s = '';
	foo($s);
	(string) $s;
};

function () {
	/** @var int|string $s */
	$s = doFoo();
	if (!is_numeric($s)) {
		(string) $s;
	}
};
