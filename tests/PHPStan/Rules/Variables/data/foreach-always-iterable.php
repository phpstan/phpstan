<?php

function () {
	foreach ([] as $val) {
		$test = 1;
	}

	echo $val;
	echo $test;
};


function () {
	foreach ([1, 2, 3] as $val) {
		$test = 1;
	}

	echo $val;
	echo $test;
};

function () {
	foreach ([1, 2, 3] as $val) {
		if (rand(0, 1)) {
			break;
		}
		$test = 1;
	}

	echo $val;
	echo $test;
};
