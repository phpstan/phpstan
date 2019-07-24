<?php

namespace PHPStan\Generics\VaryingAcceptor;

class A {}
class B extends A {}

/**
 * @template T
 *
 * @param callable(T):void $t1
 * @param T $t2
 */
function apply(callable $t1, $t2) : void
{
  $t1($t2);
}

/**
 * @template T
 *
 * @param T $t2
 * @param callable(T):void $t1
 */
function applyReversed($t2, callable $t1) : void
{
  $t1($t2);
}

function testApply()
{
	apply(function(A $_i) : void {}, new A());
	apply(function(B $_i) : void {}, new B());

	apply(function(A $_i) : void {}, new B());
	apply(function(B $_i) : void {}, new A());

	applyReversed(new A(), function(A $_i) : void {});
	applyReversed(new B(), function(B $_i) : void {});

	applyReversed(new B(), function(A $_i) : void {});
	applyReversed(new A(), function(B $_i) : void {});
}

/**
 * @template T
 *
 * @param callable(callable():T):T $closure
 * @return T
 */
function bar(callable $closure) { throw new \Exception(); }

/** @param callable(callable():int):string $callable */
function testBar($callable): void {
	bar($callable);
}
