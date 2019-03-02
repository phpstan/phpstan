<?php

namespace LoopVariables;

function () {
	$foo = null;
	$i = 0;
	$nullableVal = null;
	$falseOrObject = false;
	$anotherFalseOrObject = false;
	do {
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

		if ($anotherFalseOrObject === false) {
			$anotherFalseOrObject = new Foo();
		}

		if (doFoo()) {
			break;
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

		$i++;

		'end';
	} while (doFoo() && $i++ < 10);

	'afterLoop';
};
