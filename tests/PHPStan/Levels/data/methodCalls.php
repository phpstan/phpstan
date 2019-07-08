<?php

namespace Levels\MethodCalls;

class Foo
{

	public function doFoo(int $i)
	{
		$this->doFoo($i);
		$this->doFoo();
		$this->doFoo(1.1);

		$foo = new self();
		$foo->doFoo();
	}

}

class Bar
{

	public static function doBar(int $i)
	{
		Bar::doBar($i);
		Bar::doBar();
		Lorem::doBar();

		$bar = new Bar();
		$bar::doBar($i);
		$bar::doBar();
	}

}

class Baz
{

	/**
	 * @param Foo|Bar $fooOrBar
	 * @param Foo|null $fooOrNull
	 * @param Foo|Bar|null $fooOrBarOrNull
	 * @param Bar|Baz $barOrBaz
	 */
	public function doBaz(
		$fooOrBar,
		?Foo $fooOrNull,
		$fooOrBarOrNull,
		$barOrBaz
	)
	{
		$fooOrBar->doFoo(1);
		$fooOrBar->doFoo();
		$fooOrBar->doBaz();

		$fooOrNull->doFoo();
		$fooOrNull->doFoo(1);

		$fooOrBarOrNull->doFoo();
		$fooOrBarOrNull->doFoo(1);

		$barOrBaz->doFoo();
	}

}

class ClassWithMagicMethod
{

	public function doFoo()
	{
		$this->test();
	}

	public function __call(string $name, array $args)
	{

	}

}

class AnotherClassWithMagicMethod
{

	public function doFoo()
	{
		self::test();
	}

	public static function __callStatic(string $name, array $args)
	{

	}

}

class Ipsum
{

	/**
	 * @return Foo|Bar
	 */
	private function makeFooOrBar()
	{
		if (rand(0, 1) === 0) {
			return new Foo();
		} else {
			return new Bar();
		}
	}

	/**
	 * @return Foo|null
	 */
	private function makeFooOrNull()
	{
		if (rand(0, 1) === 0) {
			return new Foo();
		} else {
			return null;
		}
	}

	/**
	 * @return Foo|Bar|null
	 */
	public function makeFooOrBarOrNull()
	{
		if (rand(0, 1) === 0) {
			return new Foo();
		} elseif (rand(0, 1) === 1) {
			return new Bar();
		} else {
			return null;
		}
	}

	/**
	 * @return Bar|Baz
	 */
	public function makeBarOrBaz()
	{
		if (rand(0, 1) === 0) {
			return new Bar();
		} else {
			return new Baz();
		}
	}

	public function doLorem()
	{
		$fooOrBar = $this->makeFooOrBar();
		$fooOrBar->doFoo(1);
		$fooOrBar->doFoo();
		$fooOrBar->doBaz();

		$fooOrNull = $this->makeFooOrNull();
		$fooOrNull->doFoo();
		$fooOrNull->doFoo(1);

		$fooOrBarOrNull = $this->makeFooOrBarOrNull();
		$fooOrBarOrNull->doFoo();
		$fooOrBarOrNull->doFoo(1);

		$barOrBaz = $this->makeBarOrBaz();
		$barOrBaz->doFoo();
	}

}

class FooException extends \Exception
{

	public function commonMethod()
	{

	}

	public function doFoo()
	{

	}

}

class BarException extends \Exception
{

	public function commonMethod()
	{

	}

	public function doBar()
	{

	}

}

class TestExceptions
{

	public function doFoo()
	{
		try {

		} catch (FooException | BarException $e) {
			$e->commonMethod();
			$e->doFoo();
			$e->doBar();
			$e->doBaz();
		}
	}

}

class ExtraArguments
{

	public function doFoo(int $i)
	{
		$this->doFoo();
		$this->doFoo(1);
		$this->doFoo(1, 2);
	}

}
