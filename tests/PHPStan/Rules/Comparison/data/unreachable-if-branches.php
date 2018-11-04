<?php

function (\stdClass $std) {

	if ($std instanceof \stdClass) {

	}

};

function (\stdClass $std) {

	if ($std instanceof \stdClass) {

	} else { // unreachable

	}

};

function (\stdClass $std, string $str) {

	if ($std instanceof \stdClass) {

	} elseif ($str) { // unreachable

	} else { // unreachable

	}

};

function (\stdClass $std, string $foo, string $bar) {

	if ($foo) {

	} elseif ($std instanceof \stdClass) {

	} elseif ($bar) { // unreachable

	} else { // unreachable

	}

};
