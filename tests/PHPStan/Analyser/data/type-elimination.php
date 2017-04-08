<?php

namespace TypeElimination;

/** @var Foo|null $foo */
$foo = doFoo();

if ($foo === null) {
	'nullForSure';
}

if ($foo !== null) {
	'notNullForSure';
}

if (null === $foo) {
	'yodaNullForSure';
}

if (null !== $foo) {
	'yodaNotNullForSure';
}
