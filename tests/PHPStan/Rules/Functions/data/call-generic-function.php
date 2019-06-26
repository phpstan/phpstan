<?php

namespace CallGenericFunction;

/**
 * @template A
 * @template B
 * @param int|array<A> $a
 * @param int|array<B> $b
 */
function f($a, $b): void {
}

function test(): void {
	f(1, 2);
}

/**
 * @template A of \DateTime
 * @param A $a
 */
function g($a): void {
}

function testg(): void {
	g(new \DateTimeImmutable());
}
