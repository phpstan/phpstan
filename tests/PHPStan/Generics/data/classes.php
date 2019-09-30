<?php

namespace PHPStan\Generics\Classes;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;

/**
 * class A
 *
 * @template T
 */
class A {
	/** @var T */
	private $a;

	/** @var T */
	public $b;

	/** @param T $a */
	public function __construct($a) {
		$this->a = $a;
		$this->b = $a;
	}

	/**
	 * @return T
	 */
	public function get(int $i) {
		if ($i === 0) {
			return 1;
		}

		return $this->a;
	}

	/**
	 * @param T $a
	 */
	public function set($a): void {
		$this->a = $a;
	}

}

/**
 * @extends A<\DateTime>
 */
class AOfDateTime extends A {
	public function __construct() {
		parent::__construct(new \DateTime());
	}
}

/**
 * class B
 *
 * @template T
 *
 * @extends A<T>
 */
class B extends A {
	/**
	 * B::__construct()
	 *
	 * @param T $a
	 */
	public function __construct($a) {
		parent::__construct($a);
	}
}

/**
 * @template T
 */
interface I {
	/**
	 * I::set()
	 *
	 * @param T $a
	 */
	function set($a): void;

	/**
	 * I::get()
	 *
	 * @return T
	 */
	function get();
}

/**
 * Class C
 *
 * @implements I<int>
 */
class C implements I {
	/** @var int */
	private $a;

	public function set($a): void {
		$this->a = $a;
	}

	public function get() {
		return $this->a;
	}
}

/**
 * Interface SuperIfaceA
 *
 * @template T
 */
interface SuperIfaceA {
	/**
	 * SuperIfaceA::get()
	 *
	 * @return T
	 */
	public function getA();
}

/**
 * Interface SuperIfaceB
 *
 * @template T
 */
interface SuperIfaceB {
	/**
	 * SuperIfaceB::get()
	 *
	 * @return T
	 */
	public function getB();
}

/**
 * IfaceAB
 *
 * @template T
 *
 * @extends SuperIfaceA<int>
 * @extends SuperIfaceB<T>
 */
interface IfaceAB extends SuperIfaceA, SuperIfaceB {
}

/**
 * ABImpl
 *
 * @implements IfaceAB<\DateTime>
 */
class ABImpl implements IfaceAB {
	public function getA() {
		throw new \Exception();
	}
	public function getB() {
		throw new \Exception();
	}
}

/**
 * @param A<\DateTimeInterface> $a
 */
function acceptAofDateTimeInterface($a): void {
}

/**
 * @param SuperIfaceA<int> $a
 */
function acceptSuperIFaceAOfInt($a): void {
}

/**
 * @param SuperIfaceB<\DateTime> $a
 */
function acceptSuperIFaceBOfDateTime($a): void {
}

/**
 * @param SuperIfaceB<int> $a
 */
function acceptSuperIFaceBOfInt($a): void {
}

/**
 * @template TNodeType of \PhpParser\Node
 */
interface GenericRule
{

	/**
	 * @return class-string<TNodeType>
	 */
	public function getNodeType(): string;

	/**
	 * @param TNodeType $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[] errors
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array;

}

/**
 * @implements GenericRule<\PhpParser\Node\Expr\StaticCall>
 */
class SomeRule implements GenericRule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\StaticCall::class;
	}

	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		return [];
	}

}

function testClasses(): void {
	$a = new A(1);
	$a->set(2);
	$a->set($a->get(0));
	$a->set('');

	$a = new AOfDateTime();
	$a->set(new \DateTime());
	$a->set($a->get(0));
	$a->set(1);

	$b = new B(1);
	$b->set(2);
	$b->set($b->get(0));
	$b->set('');

	$c = new C();
	$c->set(2);
	$c->set($c->get());
	$c->set('');

	$ab = new ABImpl();
	acceptSuperIFaceAOfInt($ab);
	acceptSuperIFaceBOfDateTime($ab);
	acceptSuperIFaceBOfInt($ab);
}
