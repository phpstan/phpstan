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
	}

}

interface Collection extends \IteratorAggregate
{

}
