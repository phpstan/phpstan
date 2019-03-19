<?php

namespace LoopVariables;

function () {
	$foo = null;
	$nullableVal = null;
	$falseOrObject = false;
	for ($i = 0; $i < 10; $i++) {
		'begin';
		$foo = new Foo();
		'afterAssign';

		if ($nullableVal === null) {
			'nullableValIf';
			$nullableVal = 1;
		} else {
			$nullableVal *= 10;
			'nullableValElse';
		}

		if ($falseOrObject === false) {
			$falseOrObject = new Foo();
		}

		if (something()) {
			$foo = new Bar();
			break;
		}
		if (something()) {
			$foo = new Baz();
			return;
		}
		if (something()) {
			$foo = new Lorem();
			continue;
		}

		'end';
	}

	'afterLoop';
};
