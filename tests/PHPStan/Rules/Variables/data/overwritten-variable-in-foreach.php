<?php

(function () {
	foreach ([1, 2, 3] as $myVar) {
		print $myVar;
	}
})();

(function () {
	$value = 10;

	foreach ([1, 2, 3] as $value) {
		print $value;
	}

	print $value;
})();

(function () {
	$key = 10;

	foreach ([1, 2, 3] as $key => $value) {
		print $key;
	}

	print $key;
})();

(function () {
	$key = 10;
	$value = 10;

	foreach ([1, 2, 3] as $key => $value) {
		print $key;
		print $value;
	}

	print $key;
	print $value;
})();

(function () {
	foreach ([1, 2, 3] as $key => $value) {
		$key = 10;
		$value = 10;
	}
})();

(function () {
	foreach ([1, 2, 3] as $key => $value) {
		foreach ([4, 5, 6] as $key2 => $value2) {
			print $key;
			print $value;
			print $key2;
			print $value2;
		}
	}
})();

(function () {
	if (random_int(0, 1) === 0) {
		$value = 10;
	}

	foreach ([1, 2, 3] as $value) {
		print $value;
	}
})();
