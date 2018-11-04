<?php

function (array $arr) {

	foreach ($arr as $val) {
		$test = 'foo';
	}

	echo $val;
	echo $test;

};

function (array $arr) {

	if (!isset($arr['foo'])) {
		return;
	}

	foreach ($arr as $val) {
		$test = 'foo';
	}

	echo $val;
	echo $test;

};

function () {

	foreach ([1, 2, 3] as $val) {
		$test = 'foo';
	}

	echo $val;
	echo $test;

};

function () {

	foreach ([] as $val) {
		$test = 'foo';
	}

	echo $val;
	echo $test;

};

function () {

	$arr = [];
	if (rand(0, 1) === 0) {
		$arr[] = 1;
	}

	foreach ($arr as $val) {
		$test = 'foo';
	}

	echo $val;
	echo $test;

};

function (array $arr) {

	if (!isset($arr['foo'])) {
		return;
	}

	if ($arr) {
		$test = 1;
	}

	echo $test;

};
