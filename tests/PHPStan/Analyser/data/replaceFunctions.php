<?php

namespace ReplaceFunctions;

class StringConvertible {
    public function __toString(): string
    {
        return 'foo';
    }
}

function ($mixed) {

	$array = ['a' => 'a', 'b' => 'b'];
	$string = 'str';
	$int = 123;

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
	$expectedStringFromInt = str_replace('aaa', 'bbb', $int);
	$expectedStringFromStringConvertible = str_replace('aaa', 'bbb', new StringConvertible());

	$anotherExpectedString = preg_replace('aaa', 'bbb', $string);
	$anotherExpectedArray = preg_replace('aaa', 'bbb', $array);
	$anotherExpectedArrayOrString = preg_replace('aaa', 'bbb', $arrayOrString);
	$anotherExpectedStringFromInt = preg_replace('aaa', 'bbb', $int);
	$anotherExpectedStringFromStringConvertible = preg_replace('aaa', 'bbb', new StringConvertible());

	$expectedString2 = preg_replace_callback('aaa', function () {}, $string);
	$expectedArray2 = preg_replace_callback('aaa', function () {}, $array);
	$expectedArrayOrString2 = preg_replace_callback('aaa', function () {}, $arrayOrString);
	$expectedStringFromInt2 = preg_replace_callback('aaa', function () {}, $int);
	$expectedStringFromStringConvertible2 = preg_replace_callback('aaa', function () {}, new StringConvertible());

	$expectedString3 = str_ireplace('aaa', 'bbb', $string);
	$expectedArray3 = str_ireplace('aaa', 'bbb', $array);
	$expectedArrayOrString3 = str_ireplace('aaa', 'bbb', $arrayOrString);
	$expectedBenevolentArrayOrString3 = str_ireplace('aaa', 'bbb', $mixed);
	$anotherExpectedStringFromInt3 = str_ireplace('aaa', 'bbb', $int);
	$anotherExpectedStringFromStringConvertible3 = str_ireplace('aaa', 'bbb', $int);

	/** @var Foo[] $arr */
	$arr = doFoo();

	foreach ($arr as $intOrStringKey => $value) {
		die;
	}

};
