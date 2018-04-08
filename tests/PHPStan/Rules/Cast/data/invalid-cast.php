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
};
