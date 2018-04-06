<?php

function (
	string $str
) {
	(string) $str;
	(string) new \stdClass();
	(string) new \Test\ClassWithToString();
};
