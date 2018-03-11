<?php

namespace ClosurePassedByReference;

function () {

	$progressStarted = false;
	$anotherVariable = false;
	'beforeCallback';
	$callback = function () use (&$progressStarted, $anotherVariable, &$untouchedPassedByRef): void {
		'inCallbackBeforeAssign';
		if (doFoo()) {
			$progressStarted = 1;
			return;
		}
		if (!$progressStarted) {
			$progressStarted = true;
		}
		if (!$anotherVariable) {
			$anotherVariable = true;
		}

		'inCallbackAfterAssign';
	};

	'afterCallback';
};
