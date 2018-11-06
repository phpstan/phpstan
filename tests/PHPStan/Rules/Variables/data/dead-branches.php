<?php

function () {

	if (true) {
		$test = 1;
	}

	echo $test;

};

function () {

	if (true) {

	} else {
		$test = 1;
	}

	echo $test;

};

function () {

	if (false) {
		$test = 1;
	} else {

	}

	echo $test;
};

function () {

	if (false) {

	} else {
		$test = 1;
	}

	echo $test;
};

function (string $str) {

	if (true) {

	} elseif ($str) {
		$test = 1;
	}

	echo $test;

};

function (string $str) {
	if ($str) {

	} elseif (false) {
		$test = 1;
	}

	echo $test;
};

function (string $str) {
	if (false) {

	} elseif (false) {

	} elseif (true) {
		$test = 1;
	} else {

	}

	echo $test;
};

function (string $str) {
	if (false) {

	} elseif (false) {

	} elseif (true) {

	} else {
		$test = 1;
	}

	echo $test;
};
