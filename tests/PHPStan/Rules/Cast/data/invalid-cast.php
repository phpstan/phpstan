<?php

function (
	string $str
) {
	(string) $str;
	(string) new \stdClass();
	(string) new \Test\ClassWithToString();

	(object) new \stdClass();

	(float) 1.2;
	(int) $str; // ok
	(float) $str; // ok

	(int) [];

	(int) true; // ok
	(float) true; // ok
	(int) "123"; // ok
	(int) "blabla";

	(int) new \stdClass();
	(float) new \stdClass();

	(string) fopen('php://memory', 'r');
	(int) fopen('php://memory', 'r');
};

function (
	\Test\Foo $foo
) {
	/** @var object $object */
	$object = doFoo();
	(string) $object;

	if (method_exists($object, '__toString')) {
		(string) $object;
	}

	(string) $foo;
	if (method_exists($foo, '__toString')) {
		(string) $foo;
	}

	/** @var array|float|int $arrayOrFloatOrInt */
	$arrayOrFloatOrInt = doFoo();
	(string) $arrayOrFloatOrInt;
};

function (
	\SimpleXMLElement $xml
)
{
	(float) $xml;
	(int) $xml;
	(string) $xml;
	(bool) $xml;
};
