<?php

namespace ReplaceFunctions;

function () {

	$array = ['a' => 'a', 'b' => 'b'];
	$string = 'str';

	$arrayOrString = [];
	if (doFoo()) {
		$arrayOrString = 'foo';
	}

	$expectedString = str_replace('aaa', 'bbb', $string);
	$expectedArray = str_replace('aaa', 'bbb', $array);
	$expectedArrayOrString = str_replace('aaa', 'bbb', $arrayOrString);

	$anotherExpectedString = preg_replace('aaa', 'bbb', $string);
	$anotherExpectedArray = preg_replace('aaa', 'bbb', $array);
	$anotherExpectedArrayOrString = preg_replace('aaa', 'bbb', $arrayOrString);

	die;

};
