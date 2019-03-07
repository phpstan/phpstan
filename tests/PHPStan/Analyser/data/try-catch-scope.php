<?php

namespace TryCatchScope;

function () {

	$resource = null;
	try {
		$resource = new Foo();
	} catch (FooException $e) {
		$resource = new Foo();
	} catch (BarException $e) {
		$resource = new Foo();
	}

	'first';

};

function () {

	$resource = null;
	try {
		$resource = new Foo();
	} catch (FooException $e) {

	} catch (BarException $e) {
		$resource = new Foo();
	}

	'second';

};

function () {

	$resource = null;
	try {
		$resource = new Foo();
	} catch (FooException $e) {

	} catch (BarException $e) {

	}

	'third';

};
