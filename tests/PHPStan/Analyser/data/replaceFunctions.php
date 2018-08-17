<?php

namespace ReplaceFunctions;

function ($mixed) {

	$array = ['a' => 'a', 'b' => 'b'];
	$string = 'str';

	$arrayOrString = [];
	if (doFoo()) {
		$arrayOrString = 'foo';
	}

	/** @var callable[] $callbacks */
	$callbacks = [];

	$expectedString = str_replace('aaa', 'bbb', $string);
	$expectedArray = str_replace('aaa', 'bbb', $array);
	$expectedArrayOrString = str_replace('aaa', 'bbb', $arrayOrString);
	$expectedBenevolentArrayOrString = str_replace('aaa', 'bbb', $mixed);

	$anotherExpectedString = preg_replace('aaa', 'bbb', $string);
	$anotherExpectedArray = preg_replace('aaa', 'bbb', $array);
	$anotherExpectedArrayOrString = preg_replace('aaa', 'bbb', $arrayOrString);

	$expectedString2 = preg_replace_callback('aaa', function () {}, $string);
	$expectedArray2 = preg_replace_callback('aaa', function () {}, $array);
	$expectedArrayOrString2 = preg_replace_callback('aaa', function () {}, $arrayOrString);

	$expectedString3 = str_ireplace('aaa', 'bbb', $string);
	$expectedArray3 = str_ireplace('aaa', 'bbb', $array);
	$expectedArrayOrString3 = str_ireplace('aaa', 'bbb', $arrayOrString);
	$expectedBenevolentArrayOrString3 = str_ireplace('aaa', 'bbb', $mixed);

	/** @var Foo[] $arr */
	$arr = doFoo();

	foreach ($arr as $intOrStringKey => $value) {
		die;
	}

};
