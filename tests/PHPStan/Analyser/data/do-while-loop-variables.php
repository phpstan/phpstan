<?php

namespace LoopVariables;

function () {
	$foo = null;
	do {
		'begin';
		$foo = new Foo();
		'afterAssign';

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
	} while (true);

	'afterLoop';
};
