<?php

namespace PHPStan\Generics\GenericClassStringType;

use function PHPStan\Analyser\assertType;

class C
{
	public static function f(): int {
		return 0;
	}
}

/**
 * @param mixed $a
 */
function testMixed($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('class-string<DateTimeInterface>|DateTimeInterface', $a);
		assertType('DateTimeInterface', new $a());
	}

	if (is_subclass_of($a, 'DateTimeInterface') || is_subclass_of($a, 'stdClass')) {
		assertType('class-string<DateTimeInterface>|class-string<stdClass>|DateTimeInterface|stdClass', $a);
		assertType('DateTimeInterface|stdClass', new $a());
	}

	if (is_subclass_of($a, C::class)) {
		assertType('int', $a::f());
	}
}

/**
 * @param object $a
 */
function testObject($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('DateTimeInterface', $a);
	}
}

/**
 * @param string $a
 */
function testString($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('class-string<DateTimeInterface>', $a);
		assertType('DateTimeInterface', new $a());
	}

	if (is_subclass_of($a, C::class)) {
		assertType('int', $a::f());
	}
}

/**
 * @param string|object $a
 */
function testStringObject($a) {
	assertType('mixed', new $a());

	if (is_subclass_of($a, 'DateTimeInterface')) {
		assertType('class-string<DateTimeInterface>|DateTimeInterface', $a);
		assertType('DateTimeInterface', new $a());
	}

	if (is_subclass_of($a, C::class)) {
		assertType('int', $a::f());
	}
}

/**
 * @param class-string<\DateTimeInterface> $a
 */
function testClassString($a) {
	assertType('DateTimeInterface', new $a());

	if (is_subclass_of($a, 'DateTime')) {
		assertType('class-string<DateTime>', $a);
		assertType('DateTime', new $a());
	}
}
