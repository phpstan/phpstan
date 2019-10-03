<?php

namespace PHPStan\Generics\FunctionsAssertType;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Generics\FunctionsAssertType\GenericRule;
use function PHPStan\Analyser\assertType;

/**
 * @template T
 * @param T $a
 * @return T
 */
function a($a) {
	assertType('T (function PHPStan\Generics\FunctionsAssertType\a(), argument)', $a);
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
	assertType('T of DateTimeInterface (function PHPStan\Generics\FunctionsAssertType\b(), argument)', $a);
	assertType('T of DateTimeInterface (function PHPStan\Generics\FunctionsAssertType\b(), argument)', b($a));
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
	assertType('array|DateTime', d([], new \DateTime()));
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
	assertType('int', e([[$int]]));
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
	assertType('array<A (function PHPStan\Generics\FunctionsAssertType\f(), argument)>', $a);
	assertType('callable(A (function PHPStan\Generics\FunctionsAssertType\f(), argument)): B (function PHPStan\Generics\FunctionsAssertType\f(), argument)', $b);
	foreach ($a as $k => $v) {
		assertType('A (function PHPStan\Generics\FunctionsAssertType\f(), argument)', $v);
		$newV = $b($v);
		assertType('B (function PHPStan\Generics\FunctionsAssertType\f(), argument)', $newV);
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
	assertType('T of PHPStan\Generics\FunctionsAssertType\Foo (function PHPStan\Generics\FunctionsAssertType\testReturnsStatic(), argument)', $foo::returnsStatic());
	assertType('T of PHPStan\Generics\FunctionsAssertType\Foo (function PHPStan\Generics\FunctionsAssertType\testReturnsStatic(), argument)', $foo->instanceReturnsStatic());
}

/**
 * @param int[] $listOfIntegers
 */
function testArrayMap(array $listOfIntegers)
{
	$strings = array_map(function ($int): string {
		assertType('int', $int);

		return (string) $int;
	}, $listOfIntegers);
	assertType('array<string>', $strings);
}

/**
 * @param int[] $listOfIntegers
 */
function testArrayFilter(array $listOfIntegers)
{
	$integers = array_filter($listOfIntegers, function ($int): bool {
		assertType('int', $int);

		return true;
	});
	assertType('array<int>', $integers);
}

/**
 * @template K
 * @template V
 * @param iterable<K, V> $it
 * @return array<K, V>
 */
function iterableToArray($it) {
	$ret = [];
	foreach ($it as $k => $v) {
		$ret[$k] = $v;
	}
	return $ret;
}

/**
 * @param iterable<string, Foo> $it
 */
function testIterable(iterable $it) {
	assertType('array<string, PHPStan\Generics\FunctionsAssertType\Foo>', iterableToArray($it));
}

/**
 * @template T
 * @template U
 * @param array{a: T, b: U, c: int} $a
 * @return array{T, U}
 */
function constantArray($a): array {
	return [$a['a'], $a['b']];
}

function testConstantArray(int $int, string $str) {
	[$a, $b] = constantArray(['a' => $int, 'b' => $str, 'c' => 1]);
	assertType('int', $a);
	assertType('string', $b);
}

/**
 * @template U of \DateTimeInterface
 * @param U $a
 * @return U
 */
function typeHints(\DateTimeInterface $a): \DateTimeInterface {
	assertType('U of DateTimeInterface (function PHPStan\Generics\FunctionsAssertType\typeHints(), argument)', $a);
	return $a;
}

/**
 * @template U of \DateTime
 * @param U $a
 * @return U
 */
function typeHintsSuperType(\DateTimeInterface $a): \DateTimeInterface {
	assertType('U of DateTime (function PHPStan\Generics\FunctionsAssertType\typeHintsSuperType(), argument)', $a);
	return $a;
}

/**
 * Different phpDoc on purpose because of caching issue
 * @template U of \DateTimeInterface
 * @param U $a
 * @return U
 */
function typeHintsSubType(\DateTime $a): \DateTimeInterface {
	assertType('DateTime', $a);
	return $a;
}

function testTypeHints(): void {
	assertType('DateTime', typeHints(new \DateTime()));
	assertType('DateTime', typeHintsSuperType(new \DateTime()));
	assertType('DateTimeInterface', typeHintsSubType(new \DateTime()));
}

/**
 * @template T of \Exception
 * @param T $a
 * @param T $b
 * @return T
 */
function expectsException($a, $b)
{
	return $b;
}

function testUpperBounds(\Throwable $t)
{
	assertType('Exception', expectsException(new \Exception(), $t));
}

/**
 * @template T
 * @return T
 * @param callable $cb
 */
function varAnnotation($cb)
{
	/** @var T */
	$v = $cb();

	assertType('T (function PHPStan\Generics\FunctionsAssertType\varAnnotation(), argument)', $v);

	return $v;
}

/**
 * @template T
 */
class C
{
	/** @var T */
	private $a;

	/**
	 * @param T $p
	 * @param callable $cb
	 */
	public function f($p, $cb)
	{
		assertType('T (class PHPStan\Generics\FunctionsAssertType\C, argument)', $p);

		/** @var T */
		$v = $cb();

		assertType('T (class PHPStan\Generics\FunctionsAssertType\C, argument)', $v);

		assertType('T (class PHPStan\Generics\FunctionsAssertType\C, argument)', $this->a);

		$a = new class {
			/** @return T */
			public function g() {
				throw new \Exception();
			}
		};

		assertType('T (class PHPStan\Generics\FunctionsAssertType\C, argument)', $a->g());
	}
}

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

	/**
	 * A::__construct()
	 *
	 * @param T $a
	 */
	public function __construct($a) {
		$this->a = $a;
		$this->b = $a;
	}

	/**
	 * @return T
	 */
	public function get() {
		asserType('T (class PHPStan\Generics\FunctionsAssertType\A, argument)', $this->a);
		asserType('T (class PHPStan\Generics\FunctionsAssertType\A, argument)', $this->b);
		return $this->a;
	}

	/**
	 * @param T $a
	 */
	public function set($a) {
		$this->a = $a;
	}

}

/**
 * class AOfDateTime
 *
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
	 * I::get()
	 *
	 * @return T
	 */
	function get();

	/**
	 * I::getInheritdoc()
	 *
	 * @return T
	 */
	function getInheritdoc();
}

/**
 * @implements I<int>
 */
class CofI implements I {
	public function get() {
	}

	/** @inheritdoc */
	public function getInheritdoc() {
	}
}

/**
 * Interface SuperIfaceA
 *
 * @template A
 */
interface SuperIfaceA {
	/**
	 * SuperIfaceA::get()
	 *
	 * @param A $a
	 * @return A
	 */
	public function getA($a);
}

/**
 * Interface SuperIfaceB
 *
 * @template B
 */
interface SuperIfaceB {
	/**
	 * SuperIfaceB::get()
	 *
	 * @param B $b
	 * @return B
	 */
	public function getB($b);
}

/**
 * IfaceAB
 *
 * @template T
 *
 * @extends SuperIfaceA<int>
 * @implements SuperIfaceB<T>
 */
interface IfaceAB extends SuperIfaceA, SuperIfaceB {
}

/**
 * ABImpl
 *
 * @implements IfaceAB<\DateTime>
 */
class ABImpl implements IfaceAB {
	public function getA($a) {
		// assertType('int', $a);
		return 1;
	}
	public function getB($b) {
		// assertType('DateTime', $b);
		return new \DateTime();
	}
}

/**
 * @implements SuperIfaceA<int>
 */
class X implements SuperIfaceA {
	public function getA($a) {
		// assertType('int', $a);
		return 1;
	}
}

/**
 * class NoConstructor
 *
 * @template T
 *
 * @extends A<T>
 */
class NoConstructor extends A {
}

/**
 * @template T
 * @param class-string<T> $s
 * @return T
 */
function acceptsClassString(string $s)
{
	return new $s;
}

/**
 * @template U
 * @param class-string<U> $s
 * @return class-string<U>
 */
function anotherAcceptsClassString(string $s)
{
	assertType('U (function PHPStan\Generics\FunctionsAssertType\anotherAcceptsClassString(), argument)', acceptsClassString($s));
}

/**
 * @template T
 * @param T $object
 * @return class-string<T>
 */
function returnsClassString($object)
{
	return get_class($object);
}

/**
 * @template T of \Exception
 * @param class-string<T> $string
 * @return T
 */
function acceptsClassStringUpperBound($string)
{
	return new $string;
}


/**
 * @template TNodeType of \PhpParser\Node
 */
interface GenericRule
{
    /**
     * @return TNodeType
     */
    public function getNodeInstance(): Node;
}

/**
 * @implements GenericRule<\PhpParser\Node\Expr\StaticCall>
 */
class SomeRule implements GenericRule
{
    public function getNodeInstance(): Node
    {
        return new StaticCall(new Name(\stdClass::class), '__construct');
    }
}

/**
 * Infer from generic
 *
 * @template T of \DateTimeInterface
 *
 * @param A<A<T>> $x
 *
 * @return A<T>
 */
function inferFromGeneric($x) {
	return $x->get();
}

/**
 * Class Factory
 *
 * @template A
 * @template B
 */
class Factory
{
	private $a;
	private $b;

	/**
	 * @param A $a
	 * @param B $b
	 */
	public function __construct($a, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}

	/**
	 * @template C
	 * @template D
	 *
	 * @param A $a
	 * @param C $c
	 * @param D $d
	 *
	 * @return array{A, B, C, D}
	 */
	public function create($a, $c, $d): array
	{
		return [$a, $this->b, $c, $d];
	}
}

function testClasses() {
	$a = new A(1);
	assertType('PHPStan\Generics\FunctionsAssertType\A<int>', $a);
	assertType('int', $a->get());
	assertType('int', $a->b);

	$a = new AOfDateTime();
	assertType('PHPStan\Generics\FunctionsAssertType\AOfDateTime', $a);
	assertType('DateTime', $a->get());
	assertType('DateTime', $a->b);

	$b = new B(1);
	assertType('PHPStan\Generics\FunctionsAssertType\B<int>', $b);
	assertType('int', $b->get());
	assertType('int', $b->b);

	$c = new CofI();
	assertType('PHPStan\Generics\FunctionsAssertType\CofI', $c);
	assertType('int', $c->get());
	assertType('int', $c->getInheritdoc());

	$ab = new ABImpl();
	assertType('int', $ab->getA(0));
	assertType('DateTime', $ab->getB(new \DateTime()));

	$noConstructor = new NoConstructor(1);
	assertType('PHPStan\Generics\FunctionsAssertType\NoConstructor<T (class PHPStan\Generics\FunctionsAssertType\NoConstructor, parameter)>', $noConstructor);

	assertType('stdClass', acceptsClassString(\stdClass::class));
	assertType('class-string<stdClass>', returnsClassString(new \stdClass()));

	assertType('Exception', acceptsClassStringUpperBound(\Exception::class));
	assertType('Exception', acceptsClassStringUpperBound(\Throwable::class));
	assertType('InvalidArgumentException', acceptsClassStringUpperBound(\InvalidArgumentException::class));

	$rule = new SomeRule();
	assertType(StaticCall::class, $rule->getNodeInstance());

	$a = inferFromGeneric(new A(new A(new \DateTime())));
	assertType('PHPStan\Generics\FunctionsAssertType\A<DateTime>', $a);

	$factory = new Factory(new \DateTime(), new A(1));
	assertType(
		'array(DateTime, PHPStan\Generics\FunctionsAssertType\A<int>, string, PHPStan\Generics\FunctionsAssertType\A<DateTime>)',
		$factory->create(new \DateTime(), '', new A(new \DateTime()))
	);
}
