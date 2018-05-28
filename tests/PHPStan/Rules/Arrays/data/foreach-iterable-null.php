<?php

namespace IterablesInForeachPossiblyNull;

function (?int $int, ?array $array) {
	foreach ($int as $val) {

	}
	foreach ($array as $val) {

	}

	if ($array !== null) {
		foreach ($array as $val) {

		}
	}
};
