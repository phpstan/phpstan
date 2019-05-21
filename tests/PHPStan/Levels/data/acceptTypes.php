<?php

namespace Levels\AcceptTypes;

class Foo
{

	/**
	 * @param int $i
	 * @param float $j
	 * @param float|string $k
	 * @param int|null $l
	 * @param int|float $m
	 */
	public function doFoo(
		int $i,
		float $j,
		$k,
		?int $l,
		$m
	)
	{
		$this->doBar($i);
		$this->doBar($j);
		$this->doBar($k);
		$this->doBar($l);
		$this->doBar($m);
	}

	public function doBar(int $i)
	{

	}

	/**
	 * @param float|string $a
	 * @param float|string|null $b
	 * @param int $c
	 * @param int|null $d
	 */
	public function doBaz(
		$a,
		$b,
		int $c,
		?int $d
	)
	{
		$this->doLorem($a);
		$this->doLorem($b);
		$this->doLorem($c);
		$this->doLorem($d);
		$this->doIpsum($a);
		$this->doIpsum($b);
		$this->doBar(null);
	}

	/**
	 * @param int|resource $a
	 */
	public function doLorem($a)
	{

	}

	/**
	 * @param float $a
	 */
	public function doIpsum($a)
	{

	}

	/**
	 * @param int[] $i
	 * @param float[] $j
	 * @param (float|string)[] $k
	 * @param (int|null)[] $l
	 * @param (int|float)[] $m
	 */
	public function doFooArray(
		array $i,
		array $j,
		array $k,
		array $l,
		array $m
	)
	{
		$this->doBarArray($i);
		$this->doBarArray($j);
		$this->doBarArray($k);
		$this->doBarArray($l);
		$this->doBarArray($m);
	}

	/**
	 * @param int[] $i
	 */
	public function doBarArray(array $i)
	{

	}

	public function doBazArray()
	{
		$ints = [1, 2, 3];
		$floats = [1.1, 2.2, 3.3];
		$floatsAndStrings = [1.1, 2.2];
		$intsAndNulls = [1, 2, 3];
		$intsAndFloats = [1, 2, 3];
		if (rand(0, 1) === 1) {
			$floatsAndStrings[] = 'str';
			$intsAndNulls[] = null;
			$intsAndFloats[] = 1.1;
		}

		$this->doBarArray($ints);
		$this->doBarArray($floats);
		$this->doBarArray($floatsAndStrings);
		$this->doBarArray($intsAndNulls);
		$this->doBarArray($intsAndFloats);
	}

	/**
	 * @param int|null $intOrNull
	 * @param int|float $intOrFloat
	 */
	public function doBazArrayUnionItemTypes(?int $intOrNull, $intOrFloat)
	{
		$intsAndNulls = [1, 2, 3, $intOrNull];
		$intsAndFloats = [1, 2, 3, $intOrFloat];
		$this->doBarArray($intsAndNulls);
		$this->doBarArray($intsAndFloats);
	}

	/**
	 * @param array<int, mixed> $array
	 */
	public function callableArray(
		array $array
	)
	{
		$this->expectCallable($array);
		$this->expectCallable('date');
		$this->expectCallable('nonexistentFunction');
		$this->expectCallable([$this, 'doFoo']);
	}

	public function expectCallable(callable $callable)
	{

	}

	public function iterableCountable(
		iterable $iterable,
		array $array,
		string $string
	)
	{
		count($iterable);
		count($array);
		count($string);
	}

	/**
	 * @param string[] $strings
	 */
	public function benevolentUnionNotReported(array $strings)
	{
		foreach ($strings as $key => $val) {
			$this->doBar($key);
		}
	}

}

interface ParentFooInterface
{

}

interface FooInterface extends ParentFooInterface
{

}

class FooImpl implements FooInterface
{

}

class ClosureAccepts
{

	public function doFoo()
	{
		$c = function (FooInterface $x, $y): FooInterface {
			return new FooImpl();
		};
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x): FooInterface { // less parameters - OK
			return new FooImpl();
		};
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x, $y, $z): FooInterface { // more parameters - error
			return new FooImpl();
		};

		$this->doBar($c);
		$this->doBaz($c);

		$c = function (ParentFooInterface $x): FooInterface { // parameter contravariance - OK
			return new FooImpl();
		};
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooImpl $x): FooInterface { // parameter covariance - error
			return new FooImpl();
		};
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x): FooImpl { // return type covariance - OK
			return new FooImpl();
		};
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x): ParentFooInterface { // return type contravariance - error
			return new FooImpl();
		};
		$this->doBar($c);
		$this->doBaz($c);
	}

	public function doFooUnionClosures()
	{
		$closure = function (): FooInterface {
			return new FooImpl();
		};
		$c = function (FooInterface $x, $y): FooInterface {
			return new FooImpl();
		};
		if (rand(0, 1) === 0) {
			$c = $closure;
		}
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x): FooInterface { // less parameters - OK
			return new FooImpl();
		};
		if (rand(0, 1) === 0) {
			$c = $closure;
		}
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x, $y, $z): FooInterface { // more parameters - error
			return new FooImpl();
		};
		if (rand(0, 1) === 0) {
			$c = $closure;
		}
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (ParentFooInterface $x): FooInterface { // parameter contravariance - OK
			return new FooImpl();
		};
		if (rand(0, 1) === 0) {
			$c = $closure;
		}
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooImpl $x): FooInterface { // parameter covariance - error
			return new FooImpl();
		};
		if (rand(0, 1) === 0) {
			$c = $closure;
		}
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x): FooImpl { // return type covariance - OK
			return new FooImpl();
		};
		if (rand(0, 1) === 0) {
			$c = $closure;
		}
		$this->doBar($c);
		$this->doBaz($c);

		$c = function (FooInterface $x): ParentFooInterface { // return type contravariance - error
			return new FooImpl();
		};
		if (rand(0, 1) === 0) {
			$c = $closure;
		}
		$this->doBar($c);
		$this->doBaz($c);

		$c = function () {

		};
		$this->doBar($c);
		$this->doBaz($c);
	}

	/**
	 * @param \Closure(FooInterface $x, int $y): FooInterface $closure
	 */
	public function doBar(
		\Closure $closure
	)
	{

	}

	/**
	 * @param callable(FooInterface $x, int $y): FooInterface $callable
	 */
	public function doBaz(
		callable $callable
	)
	{

	}

}

class Baz
{

	public function makeInt(): int
	{
		return 1;
	}

	public function makeFloat(): float
	{
		return 1.0;
	}

	/**
	 * @return float|string
	 */
	public function makeFloatOrString()
	{
		if (rand(0, 1) === 0) {
			return 1;
		} else {
			return 'foo';
		}
	}

	/**
	 * @return float|string|null
	 */
	public function makeFloatOrStringOrNull()
	{
		if (rand(0, 1) === 0) {
			return 1;
		} else {
			return 'foo';
		}
	}

	public function makeIntOrNull(): ?int
	{
		if (rand(0, 1) === 0) {
			return 1;
		} else {
			return null;
		}
	}

	/**
	 * @return int|float
	 */
	public function makeIntOrFloat()
	{
		if (rand(0, 1) === 0) {
			return 1;
		} else {
			return 1.0;
		}
	}

	public function doFoo()
	{
		$this->doBar($this->makeInt());
		$this->doBar($this->makeFloat());
		$this->doBar($this->makeFloatOrString());
		$this->doBar($this->makeIntOrNull());
		$this->doBar($this->makeIntOrFloat());
	}

	public function doBar(int $i)
	{

	}

	public function doBaz()
	{
		$this->doLorem($this->makeFloatOrString());
		$this->doLorem($this->makeFloatOrStringOrNull());
		$this->doLorem($this->makeInt());
		$this->doLorem($this->makeIntOrNull());
		$this->doIpsum($this->makeFloatOrString());
		$this->doIpsum($this->makeFloatOrStringOrNull());
		$this->doBar(null);
	}

	/**
	 * @param int|resource $a
	 */
	public function doLorem($a)
	{

	}

	/**
	 * @param float $a
	 */
	public function doIpsum($a)
	{

	}

	/**
	 * @return int[]
	 */
	public function makeIntArray(): array
	{
		return [1, 2, 3];
	}

	/**
	 * @return float[]
	 */
	public function makeFloatArray(): array
	{
		return [1.0, 2.0, 3.0];
	}

	/**
	 * @return (float|string)[]
	 */
	public function makeFloatOrStringArray(): array
	{
		return [1.0, 2.0, '3.0'];
	}

	/**
	 * @return (int|null)[]
	 */
	public function makeIntOrNullArray(): array
	{
		return [1, 2, null];
	}

	/**
	 * @return (int|float)[]
	 */
	public function makeIntOrFloatArray(): array
	{
		return [1.0, 2.0, 3];
	}

	public function doFooArray()
	{
		$this->doBarArray($this->makeIntArray());
		$this->doBarArray($this->makeFloatArray());
		$this->doBarArray($this->makeFloatOrStringArray());
		$this->doBarArray($this->makeIntOrNullArray());
		$this->doBarArray($this->makeIntOrFloatArray());
	}

	/**
	 * @param int[] $i
	 */
	public function doBarArray(array $i)
	{

	}

}
