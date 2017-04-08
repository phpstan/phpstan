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

/** @var int|false $intOrFalse */
$intOrFalse = doFoo();
if ($intOrFalse === false) {
	'falseForSure';
}

if ($intOrFalse !== false) {
	'intForSure';
}

if (false === $intOrFalse) {
	'yodaFalseForSure';
}

if (false !== $intOrFalse) {
	'yodaIntForSure';
}

/** @var int|true $intOrTrue */
$intOrTrue = doFoo();
if ($intOrTrue === true) {
	'trueForSure';
}

if ($intOrTrue !== true) {
	'anotherIntForSure';
}

if (true === $intOrTrue) {
	'yodaTrueForSure';
}

if (true !== $intOrTrue) {
	'yodaAnotherIntForSure';
}
