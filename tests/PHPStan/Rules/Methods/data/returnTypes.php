<?php

namespace ReturnTypes;

class Foo extends FooParent implements FooInterface
{

	public function returnNothing()
	{
		return;
	}

	public function returnInteger(): int
	{
		return 1;
		return 'foo';
		$foo = function () {
			return 'bar';
		};
	}

	public function returnObject(): Bar
	{
		return 1;
		return new self();
		return new Bar();
	}

	public function returnChild(): self
	{
		return new self();
		return new FooChild();
		return new OtherInterfaceImpl();
	}

	/**
	 * @return string|null
	 */
	public function returnNullable()
	{
		return 'foo';
		return null;
	}

	public function returnInterface(): FooInterface
	{
		return new self();
	}

	/**
	 * @return void
	 */
	public function returnVoid()
	{
		return;
		return null;
		return 1;
	}

	/**
	 * @return static
	 */
	public function returnStatic(): FooParent
	{
		return parent::returnStatic();

		$parent = new FooParent();
		return $parent->returnStatic(); // the only case with wrong static base class
		return $this->returnStatic();
	}

	public function returnAlias(): Foo
	{
		return new FooAlias();
	}

	public function returnAnotherAlias(): FooAlias
	{
		return new Foo();
	}

	/**
	 * @param self[]|Collection $collection
	 * @return self[]|Collection|array
	 */
	public function returnUnionIterableType($collection)
	{
		return $collection;
		return new Collection();
		return new self();
		return [new self()];
		return new Bar();
		return [new Bar()];
		return 1;
		return;

		/** @var Bar[]|Collection $barListOrCollection */
		$barListOrCollection = doFoo();
		return $barListOrCollection;

		/** @var self[]|AnotherCollection $selfListOrAnotherCollection */
		$selfListOrAnotherCollection = doFoo();
		return $selfListOrAnotherCollection;

		/** @var self[]|Collection|AnotherCollection $selfListOrCollectionorAnotherCollection */
		$selfListOrCollectionorAnotherCollection = doFoo();
		return $selfListOrCollectionorAnotherCollection;

		/** @var Bar[]|AnotherCollection $completelyDiffernetUnionIterable */
		$completelyDiffernetUnionIterable = doFoo();
		return $completelyDiffernetUnionIterable;

		return null;
	}

	/**
	 * @param self[]|Collection $collection
	 * @return self[]|Collection|AnotherCollection|null
	 */
	public function returnUnionIterableLooserReturnType($collection)
	{
		return $collection;
		return null;
	}

	/**
	 * @return $this
	 */
	public function returnThis(): self
	{
		return $this;
		return new self();
		return 1;
		return null;

		$that = $this;
		return $that;
	}

	/**
	 * @return $this|null
	 */
	public function returnThisOrNull()
	{
		return $this;
		return new self();
		return 1;
		return null;
		return $this->returnThis();
		return $this->returnStaticThatReturnsNewStatic();
	}

	/**
	 * @return static
	 */
	public function returnStaticThatReturnsNewStatic(): self
	{
		return new static();
		return $this;
	}

	public function returnsParent(): parent
	{
		return new FooParent();
		return 1;
		return null;
	}

	public function returnsPhpDocParent(): parent
	{
		return new FooParent();
		return 1;
		return null;
	}

	/**
	 * @return scalar
	 */
	public function returnScalar()
	{
		return 1;
		return 10.1;
		return 'a';
		return false;
		return new \stdClass();
	}

	/**
	 * @return int
	 */
	public function containsYield()
	{
		yield 1;
		return;
	}

	public function returnsNullInTernary(): int
	{
		/** @var int|null $intOrNull */
		$intOrNull = doFoo();
		return $intOrNull;
		return $intOrNull !== null ? $intOrNull : 5;
		return $intOrNull !== null ? $intOrNull : null;
	}

	public function misleadingBoolReturnType(): boolean
	{
		return true;
		return 1;
		return new boolean();
	}

	public function misleadingIntReturnType(): integer
	{
		return 1;
		return true;
		return new integer();
	}

	public function misleadingMixedReturnType(): mixed
	{
		return 1;
		return true;
		return new mixed();
	}
}

class FooChild extends Foo
{

}

class Stock
{

	/** @var self */
	private $stock;

	/** @var self|null */
	private $nullableStock;

	public function getActualStock(): self
	{
		if (is_null($this->stock))
		{
			$this->stock = $this->findStock();
			if (is_null($this->stock)) {
				throw new \Exception();
			}
			return $this->stock;
		}
		return $this->stock;
	}

	/**
	 * @return self|null
	 */
	public function findStock()
	{
		return new self();
	}

	public function getAnotherStock(): self
	{
		return $this->findStock();
	}

	public function returnSelf(): self
	{
		$stock = $this->findStock();
		if ($stock === null) {
			$stock = new self();
		}

		return $stock;
	}

	public function returnSelfAgain(): self
	{
		$stock = $this->findStock();
		if ($stock === null) {
			$stock = new self();
		} elseif (test()) {
			doFoo();
		}

		return $stock;
	}

	public function returnSelfYetAgain(): self
	{
		$stock = $this->findStock();
		if ($stock === null) {
			$stock = new self();
		} elseif (test()) {
			doFoo();
		} else {
			doBar();
		}

		return $stock;
	}

	public function returnSelfYetYetAgain(): self
	{
		if ($this->nullableStock === null) {
			$this->nullableStock = new self();
		}

		return $this->nullableStock;
	}

	public function returnSelfAgainError(): self
	{
		$stock = $this->findStock();
		if (doFoo()) {
			$stock = new self();
		}

		return $stock; // still possible null
	}

	public function returnsSelfAgainAgain(): self
	{
		while (true) {
			try {
				if ($this->getActualStock() === null) {
					continue;
				}
			} catch (\Exception $ex) {
				continue;
			}
			return $this->getActualStock();
		}
	}

	public function returnYetSelfAgainError(): self
	{
		$stock = $this->findStock();
		if ($stock === false) {
			$stock = new self();
		}

		return $stock; // still possible null
	}

}

class Issue105
{
	/**
	 * @param string $type
	 *
	 * @return array|float|int|null|string
	 */
	public function manyTypes(string $type)
	{

	}

	/**
	 * @return array
	 */
	public function returnArray(): array
	{
		$result = $this->manyTypes('array');
		$result = is_array($result) ? $result : [];

		return $result;
	}

	public function returnAnotherArray(): array
	{
		$result = $this->manyTypes('array');
		if (!is_array($result)) {
			$result = [];
		}

		return $result;
	}
}

class ReturningSomethingFromConstructor
{

	public function __construct()
	{
		return new Foo();
	}

}

class WeirdReturnFormat
{

	/**
	 * @return \PHPStan\Foo\Bar |
	 *         \PHPStan\Foo\Baz
	 */
	public function test()
	{
		return 1;
	}

}

class Collection implements \IteratorAggregate
{

	public function getIterator()
	{
		return new \ArrayIterator([]);
	}

}

class AnotherCollection implements \IteratorAggregate
{

	public function getIterator()
	{
		return new \ArrayIterator([]);
	}

}

class GeneratorMethod
{

	public function doFoo(): \Generator
	{
		return false;
		yield "foo";
	}

}

class ReturnTernary
{

	/**
	 * @param Foo|false $fooOrFalse
	 * @return Foo
	 */
	public function returnTernary($fooOrFalse): Foo
	{
		return $fooOrFalse ?: new Foo();
		return $fooOrFalse !== false ? $fooOrFalse : new Foo();

		$fooOrFalse ? ($fooResult = $fooOrFalse) : new Foo();
		return $fooResult;

		$fooOrFalse ? false : ($falseResult = $fooOrFalse);
		return $falseResult;
	}

	/**
	 * @return static|null
	 */
	public function returnStatic()
	{
		$out = doFoo();

		return is_a($out, static::class, false) ? $out : null;
	}

}

class TrickyVoid
{

	/**
	 * @return int|void
	 */
	public function returnVoidOrInt()
	{
		return;
		return  1;
		return 'str';
	}

}

class TernaryWithJsonEncode
{

	public function toJsonOrNull(array $arr, string $s): ?string
	{
		return json_encode($arr) ?: null;
		return json_encode($arr) ? json_encode($arr): null;
		return (rand(0, 1) ? $s : false) ?: null;
	}

	public function toJson(array $arr): string
	{
		return json_encode($arr) ?: '';
		return json_encode($arr) ? json_encode($arr) : '';
		return json_encode($arr) ?: json_encode($arr);
	}

}

class AppendedArrayReturnType
{

	/** @return int[] */
	public function foo() : array {
		$arr = [];
		$arr[] = new \stdClass();
		return $arr;
	}

	/**
	 * @param int[] $arr
	 * @return int[]
	 */
	public function bar(array $arr): array
	{
		$arr[] = new \stdClass();
		return $arr;
	}

}

class WrongMagicMethods
{

	public function __toString()
	{
		return true;
	}

	public function __isset($name)
	{
		return 42;
	}

	public function __destruct()
	{
		return 1;
	}

	public function __unset($name)
	{
		return 1;
	}

	public function __sleep()
	{
		return [
			new \stdClass(),
		];
	}

	public function __wakeup()
	{
		return 1;
	}

	public function __set_state(array $properties)
	{
		return ['foo' => 'bar'];
	}

	public function __clone()
	{
		return 1;
	}

}

class ReturnSpecifiedMethodCall
{

	/**
	 * @return string|false
	 */
	public function stringOrFalse()
	{

	}

	public function doFoo(): string
	{
		if ($this->stringOrFalse()) {
			return $this->stringOrFalse();
		}

		if (is_string($this->stringOrFalse())) {
			return $this->stringOrFalse();
		}

		return '';
	}

}

class ArrayFillKeysIssue
{
	/**
	 * @param string[] $stringIds
	 *
	 * @return array<string, Foo[]>
	 */
	public function getIPs(array $stringIds)
	{
		$paired = array_fill_keys($stringIds, []);
		foreach ($stringIds as $id) {
			$paired[$id][] = new Foo();
		}
		return $paired;
	}

	/**
	 * @param string[] $stringIds
	 *
	 * @return array<string, Foo[]>
	 */
	public function getIPs2(array $stringIds)
	{
		$paired = array_fill_keys($stringIds, []);
		foreach ($stringIds as $id) {
			$paired[$id][] = new Bar();
		}
		return $paired;
	}
}

class AssertThisInstanceOf
{

	/**
	 * @return $this
	 */
	public function doFoo()
	{
		assert($this instanceof FooInterface);
		return $this;
	}

	/**
	 * @return $this
	 */
	public function doBar()
	{
		$otherInstance = new self();
		assert($otherInstance instanceof FooInterface);
		return $otherInstance;
	}

}

class NestedArrayCheck
{

	/**
	 * @return array<string,bool>
	 */
	public function doFoo()
	{
		$rows = [];
		$entities = [];

		foreach ($rows as $row) {
			$entities['string'][] = 'string';
		}

		return $entities;
	}

	/**
	 * @return array<string,bool>
	 */
	public function doBar()
	{
		$rows = [];
		$entities = [];

		foreach ($rows as $row) {
			$entities['string']['foo'] = 'string';
		}

		return $entities;
	}

}

class CheckNullWithConstantType
{

	const SOME_NULL_CONST = null;

	public function doFoo(?array $nullableArray): array
	{
		if ($nullableArray === self::SOME_NULL_CONST) {
			$nullableArray = [];
		}

		return $nullableArray;
	}

}

class NullConditionInDoWhile
{

	public function doFoo(): string
	{
		do {
			$string = $this->doBar();
		} while ($string === null);

		return $string;
	}

	public function doBar(): ?string
	{

	}

}

class RecursiveStaticResolving
{
	/**
	 * @return $this
	 */
	public function f2(): self
	{
		return $this;
	}

	/**
	 * @return $this
	 */
	public function f3(): self
	{
		return $this;
	}

	/**
	 * @return $this
	 */
	public function f1(): self
	{
		return $this->f2()->f3();
	}
}
