<?php

namespace PHPStan\Generics\Pick;

class A {}
class B {}
class C {}

/**
 * @template T
 * @param T $t1
 * @param T $t2
 * @return T
 */
function pick($t1, $t2) {
  return rand(0, 1) ? $t1 : $t2;
}

/** @param A|B $c */
function foo($c) : void {}

function test() {
	foo(pick(new A, new B));
	foo(pick(new A, new C));
}
