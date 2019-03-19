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

function () {
	if (rand(0, 1) === 0) {
		$key = 1;
		$test = 1;
	}

	foreach ([] as $key => $val) {
		$test = 1;
	}

	echo $key;
	echo $test;
};

function (array $array) {
	if (rand(0, 1) === 0) {
		$key = 1;
		$test = 1;
	}

	foreach ($array as $key => $val) {
		$test = 1;
	}

	echo $key;
	echo $test;
};

function () {
	if (rand(0, 1) === 0) {
		$key = 1;
		$test = 1;
	}

	foreach ([1, 2, 3] as $key => $val) {
		$test = 1;
	}

	echo $key;
	echo $test;
};
