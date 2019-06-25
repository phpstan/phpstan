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
