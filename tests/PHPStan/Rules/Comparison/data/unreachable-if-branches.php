<?php

function () {

	if (true) {

	}

};

function () {

	if (true) {

	} else { // unreachable

	}

};

function (string $str) {

	if (true) {

	} elseif ($str) { // unreachable

	} else { // unreachable

	}

};

function (string $foo, string $bar) {

	if ($str) {

	} elseif (true) {

	} elseif ($bar) { // unreachable

	} else { // unreachable

	}

};
