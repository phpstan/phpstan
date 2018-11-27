<?php

namespace LoopVariables;

function () {
	$foo = null;
	$i = 0;
	$nullableVal = null;
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

	$bar = false;
	do {
		if (something()) {
			$bar = true;
			continue;
		}

		'afterContinueInLoopWithEarlyTermination';

		return;
	} while (condition());
};
