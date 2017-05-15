<?php

namespace TypeElimination;

class Foo
{

	public function getValue(): string
	{

	}

}

function () {
	/** @var Foo|null $foo */
	$foo = doFoo();

	if ($foo === null) {
		'nullForSure';
	}

	if ($foo !== null) {
		'notNullForSure';
	}

	if ($foo) {
		'notNullForSure2';
	} else {
		'nullForSure2';
	}

	if (!$foo) {
		'nullForSure3';
	} else {
		'notNullForSure3';
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

	if (!is_bool($intOrFalse)) {
		'yetAnotherIntForSure';
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

	if (!is_bool($intOrTrue)) {
		'yetYetAnotherIntForSure';
	}

	/** @var Foo|Bar|Baz $fooOrBarOrBaz */
	$fooOrBarOrBaz = doFoo();
	if ($fooOrBarOrBaz instanceof Foo) {
		'fooForSure';
	} else {
		'barOrBazForSure';
	}

	if ($fooOrBarOrBaz instanceof Foo) {
		// already tested
	} elseif ($fooOrBarOrBaz instanceof Bar) {
		'barForSure';
	} else {
		'bazForSure';
	}

	if (!$fooOrBarOrBaz instanceof Foo) {
		'anotherBarOrBazForSure';
	} else {
		'anotherFooForSure';
	}

	/** @var Foo|string|null $value */
	$value = doFoo();
	$result = $value instanceof Foo ? $value->getValue() : $value;
	'stringOrNullForSure';

	/** @var Foo|string|null $fooOrStringOrNull */
	$fooOrStringOrNull = doFoo();
	if ($fooOrStringOrNull === null || $fooOrStringOrNull instanceof Foo) {
		'fooOrNull';
		return;
	} else {
		'stringForSure';
	}

	'anotherStringForSure';
};
