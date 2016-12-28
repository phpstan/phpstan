<?php

if (!is_int($integer)) {
	throw new \Exception();
}

if (!is_integer($anotherInteger)) {
	throw new \Exception();
}

if (!is_long($longInteger)) {
	throw new \Exception();
}

if (!is_float($float)) {
	throw new \Exception();
}

if (!is_double($doubleFloat)) {
	throw new \Exception();
}

if (!is_real($realFloat)) {
	throw new \Exception();
}

if (!is_null($null)) {
	throw new \Exception();
}

if (!is_array($array)) {
	throw new \Exception();
}

if (!is_bool($bool)) {
	throw new \Exception();
}

if (!is_callable($callable)) {
	throw new \Exception();
}

if (!is_resource($resource)) {
	throw new \Exception();
}

if (!is_string($string)) {
	throw new \Exception();
}

if (!is_int($mixedInteger) && !ctype_digit($whatever)) {
	return;
}

assert(is_int($yetAnotherInteger));

die;
