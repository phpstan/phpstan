<?php

namespace DefinedVariablesBooleanOperatorTruthyFalsey;

function (bool $a, string $subject) {
	if ($a && preg_match('#a#', $subject, $matches)) {
		var_dump($matches);
	} else {
		var_dump($matches);
	}
};

function (bool $a, string $subject) {
	if ($a || preg_match('#a#', $subject, $matches)) {
		var_dump($matches);
	} else {
		var_dump($matches);
	}
};

function (bool $a, string $subject) {
	if (preg_match('#a#', $subject, $matches) && $a) {
		var_dump($matches);
	} else {
		var_dump($matches);
	}
};

function (bool $a, string $subject) {
	if (preg_match('#a#', $subject, $matches) || $a) {
		var_dump($matches);
	} else {
		var_dump($matches);
	}
};
