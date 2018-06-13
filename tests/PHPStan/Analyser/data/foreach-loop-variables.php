<?php

namespace LoopVariables;

function () {
	$foo = null;
	$key = null;
	$val = null;

	$integers = [];
	$i = 0;
	foreach ([1, 2, 3] as $key => $val) {
		'begin';
		$foo = new Foo();
		'afterAssign';

		$foo && $i++;

		$nullableInt = $val;
		if (rand(0, 1) === 1) {
			$nullableInt = null;
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

		if ($nullableInt === null) {
			continue;
		}

		$integers[] = $nullableInt;

		'end';
	}

	$emptyForeachKey = null;
	$emptyForeachVal = null;
	foreach ([1, 2, 3] as $emptyForeachKey => $emptyForeachVal) {

	}

	'afterLoop';
};
