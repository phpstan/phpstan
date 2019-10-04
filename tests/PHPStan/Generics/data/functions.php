<?php

namespace PHPStan\Generics\Functions;

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
	foreach ($a as $k => $v) {
		$newV = $b($v);
		$result[$k] = $newV;
	}
	return $result;
}

/**
 * @param array<int> $arrayOfInt
 * @param null|(callable(int):string) $callableOrNull
 */
function testF($arrayOfInt, $callableOrNull) {
	f($arrayOfInt, function (int $a): string {
		return (string) $a;
	});
	f($arrayOfInt, function ($a): string {
		return (string) $a;
	});
	f($arrayOfInt, function ($a) {
		return $a;
	});
	f($arrayOfInt, $callableOrNull);
	f($arrayOfInt, null);
	f($arrayOfInt, '');
}

/**
 * Function passthru
 *
 * @template T of \DateTimeInterface
 *
 * @param T $t
 */
function passthru($t): void {
	passthru2($t);
}

/**
 * Function passthru2
 *
 * @template T of \DateTimeInterface
 *
 * @param T $t
 */
function passthru2($t): void {
}
