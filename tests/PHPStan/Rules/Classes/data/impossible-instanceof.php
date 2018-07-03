<?php

namespace ImpossibleInstanceOf;

interface Foo
{

}

interface Bar
{

}

interface BarChild extends Bar
{

}

class Lorem
{

}

class Ipsum extends Lorem
{

}

class Dolor
{

}

class FooImpl implements Foo
{

}

class Test
{

	public function doTest(
		Foo $foo,
		Bar $bar,
		Lorem $lorem,
		Ipsum $ipsum,
		Dolor $dolor,
		FooImpl $fooImpl,
		BarChild $barChild
	)
	{
		if ($foo instanceof Bar) {

		}
		if ($bar instanceof Foo) {

		}
		if ($lorem instanceof Lorem) {

		}
		if ($lorem instanceof Ipsum) {

		}
		if ($ipsum instanceof Lorem) {

		}
		if ($ipsum instanceof Ipsum) {

		}
		if ($dolor instanceof Lorem) {

		}
		if ($fooImpl instanceof Foo) {

		}
		if ($barChild instanceof Bar) {

		}

		/** @var Collection|mixed[] $collection */
		$collection = doFoo();
		if ($collection instanceof Foo) {

		}

		/** @var object $object */
		$object = doFoo();
		if ($object instanceof Foo) {

		}

		$str = 'str';
		if ($str instanceof Foo) {

		}

		if ($str instanceof $str) {

		}

		if ($foo instanceof $str) {

		}

		$self = new self();
		if ($self instanceof self) {

		}
	}

	public function foreachWithTypeChange()
	{
		$foo = null;
		foreach ([] as $val) {
			if ($foo instanceof self) {

			}
			if ($foo instanceof Lorem) {

			}

			$foo = new self();
			if ($foo instanceof self) {

			}
		}
	}

	public function whileWithTypeChange()
	{
		$foo = null;
		while (fetch()) {
			if ($foo instanceof self) {

			}
			if ($foo instanceof Lorem) {

			}

			$foo = new self();
			if ($foo instanceof self) {

			}
		}
	}

	public function forWithTypeChange()
	{
		$foo = null;
		for (;;) {
			if ($foo instanceof self) {

			}
			if ($foo instanceof Lorem) {

			}

			$foo = new self();
			if ($foo instanceof self) {

			}
		}
	}

}

interface Collection extends \IteratorAggregate
{

}

final class FinalClassWithInvoke
{

	public function __invoke()
	{

	}

}

final class FinalClassWithoutInvoke
{

}

class ClassWithInvoke
{

	public function __invoke()
	{

	}

	public function doFoo(callable $callable, Foo $foo)
	{
		if ($callable instanceof self) {

		}
		if ($callable instanceof FinalClassWithInvoke) {

		}
		if ($callable instanceof FinalClassWithoutInvoke) {

		}
		if ($callable instanceof Foo) {

		}
		if ($callable instanceof Lorem) {

		}
	}

}

class EliminateCompoundTypes
{

	/**
	 * @param Lorem|Dolor $union
	 * @param Foo&Bar $intersection
	 */
	public function doFoo($union, $intersection)
	{
		if ($union instanceof Lorem || $union instanceof Dolor) {

		} elseif ($union instanceof Lorem) {

		}

		if ($intersection instanceof Foo && $intersection instanceof Bar) {

		} elseif ($intersection instanceof Foo) {

		}

		if ($intersection instanceof Foo) {

		} elseif ($intersection instanceof Bar) {

		}
	}

}

class InstanceOfString
{

	/**
	 * @param Foo|Bar|null $fooBarNull
	 */
	public function doFoo($fooBarNull)
	{
		$string = 'Foo';
		if (rand(0, 1) === 1) {
			$string = 'Bar';
		}
		if ($fooBarNull instanceof $string) {
			return;
		}
	}

}

trait TraitWithInstanceOfThis
{

	public function doFoo()
	{
		if ($this instanceof Foo) {

		}
	}

}

class ClassUsingTrait implements Foo
{

	use TraitWithInstanceOfThis;

}
