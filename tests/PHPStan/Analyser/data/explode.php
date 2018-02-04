<?php

namespace ExplodeFunction;

function (string $delimiter) {

	/** @var string $str */
	$str = doFoo();
	$sureArray = explode(' ', $str);
	$sureFalse = explode('', $str);
	$arrayOrFalse = explode($delimiter, $str);

	$emptyOrComma = '';
	if (doFoo()) {
		$emptyOrComma = ',';
	}

	$anotherArrayOrFalse = explode($emptyOrComma, $str);

	die;

};
