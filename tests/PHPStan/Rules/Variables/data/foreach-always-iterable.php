<?php

function () {
	foreach ([] as $key => $val) {
		$test = 1;
	}

	echo $key;
	echo $val;
	echo $test;
};


function () {
	foreach ([1, 2, 3] as $key => $val) {
		$test = 1;
	}

	echo $key;
	echo $val;
	echo $test;
};

function () {
	foreach ([1, 2, 3] as $key => $val) {
		if (rand(0, 1)) {
			break;
		}
		$test = 1;
	}

	echo $key;
	echo $val;
	echo $test;
};
