<?php

namespace PHPStan\Generics\FunctionsAssertType;

use function PHPStan\Analyser\assertType;

/**
 * @template T
 * @param T $a
 * @return T
 */
function a($a) {
	assertType('T (function PHPStan\Generics\FunctionsAssertType\a())', $a);
	return $a;
}

/**
 * @param int $int
 * @param int|float $intFloat
 * @param mixed $mixed
 */
function testA($int, $intFloat, $mixed) {
	assertType('int', a($int));
	assertType('float|int', a($intFloat));
	assertType('DateTime', a(new \DateTime()));
	assertType('mixed', a($mixed));
}

/**
 * @template T of \DateTimeInterface
 * @param T $a
 * @return T
 */
function b($a) {
	assertType('T of DateTimeInterface (function PHPStan\Generics\FunctionsAssertType\b())', $a);
	assertType('T of DateTimeInterface (function PHPStan\Generics\FunctionsAssertType\b())', b($a));
	return $a;
}

/**
 * @param \DateTimeInterface $dateTimeInterface
 */
function assertTypeTest($dateTimeInterface) {
	assertType('DateTime', b(new \DateTime()));
	assertType('DateTimeImmutable', b(new \DateTimeImmutable()));
	assertType('DateTimeInterface', b($dateTimeInterface));
}

/**
 * @template K
 * @template V
 * @param array<K,V> $a
 * @return array<K,V>
 */
function c($a) {
	return $a;
}

/**
 * @param array<int, string> $arrayOfString
 */
function testC($arrayOfString) {
	assertType('array<int, string>', c($arrayOfString));
}

/**
 * @template T
 * @param T $a
 * @param T $b
 * @return T
 */
function d($a, $b) {
	return $a;
}

/**
 * @param int $int
 * @param float $float
 * @param int|float $intFloat
 */
function testD($int, $float, $intFloat) {
	assertType('int', d($int, $int));
	assertType('float|int', d($int, $float));
	assertType('DateTime|int', d($int, new \DateTime()));
	assertType('DateTime|float|int', d($intFloat, new \DateTime()));
	assertType('array()|DateTime', d([], new \DateTime()));
}

/**
 * @template T
 * @param array<\DateTime|array<T>> $a
 * @return T
 */
function e($a) {
	throw new \Exception();
}

/**
 * @param int $int
 */
function testE($int) {
	assertType('*NEVER*', e([[$int]]));
}

/**
 * @template A
 * @template B
 *
 * @param array<A> $a
 * @param callable(A):B $b
 *
 * @return array<B>
 */
function f($a, $b) {
	$result = [];
	assertType('array<A (function PHPStan\Generics\FunctionsAssertType\f())>', $a);
	assertType('callable(A (function PHPStan\Generics\FunctionsAssertType\f())): B (function PHPStan\Generics\FunctionsAssertType\f())', $b);
	foreach ($a as $k => $v) {
		assertType('A (function PHPStan\Generics\FunctionsAssertType\f())', $v);
		$newV = $b($v);
		assertType('B (function PHPStan\Generics\FunctionsAssertType\f())', $newV);
		$result[$k] = $newV;
	}
	return $result;
}

/**
 * @param array<int> $arrayOfInt
 * @param null|(callable(int):string) $callableOrNull
 */
function testF($arrayOfInt, $callableOrNull) {
	assertType('array<string>', f($arrayOfInt, function (int $a): string {
		return (string) $a;
	}));
	assertType('array<string>', f($arrayOfInt, function ($a): string {
		return (string) $a;
	}));
	assertType('array', f($arrayOfInt, function ($a) {
		return $a;
	}));
	assertType('array<string>', f($arrayOfInt, $callableOrNull));
	assertType('array', f($arrayOfInt, null));
	assertType('array', f($arrayOfInt, ''));
}

/**
 * @template T
 * @param T $a
 * @return array<T>
 */
function g($a) {
	return [$a];
}

/**
 * @param int $int
 */
function testG($int) {
	assertType('array<int>', g($int));
}

class Foo {
	/** @return static */
	public static function returnsStatic() {
		return new static();
	}

	/** @return static */
	public function instanceReturnsStatic() {
		return new static();
	}
}

/**
 * @template T of Foo
 * @param T $foo
 */
function testReturnsStatic($foo) {
	assertType('T of PHPStan\Generics\FunctionsAssertType\Foo (function PHPStan\Generics\FunctionsAssertType\testReturnsStatic())', $foo::returnsStatic());
	assertType('T of PHPStan\Generics\FunctionsAssertType\Foo (function PHPStan\Generics\FunctionsAssertType\testReturnsStatic())', $foo->instanceReturnsStatic());
}
