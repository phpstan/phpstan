<?php

namespace ClosurePassedByReference;

function () {
	$fooOrNull = null;
	'beforeCallback';
	$callback = function () use (&$fooOrNull): void {
		'inCallbackBeforeAssign';
		if ($fooOrNull === null) {
			$fooOrNull = new Foo();
		}

		'inCallbackAfterAssign';

		return $fooOrNull;
	};

	'afterCallback';
};
