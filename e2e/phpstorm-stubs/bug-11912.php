<?php

namespace Bug11912;

/**
 * @param array<string, mixed> $results
 * @param list<string> $names
 */
function appendResults(array $results, array $names): null {
	// Make sure 'names' comes first in array
	$results = ['names' => $names] + $results;
	\PHPStan\Testing\assertType("list<string>", $results['names']);
	return null;
}
