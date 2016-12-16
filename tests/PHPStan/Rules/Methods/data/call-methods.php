<?php

namespace Test;

class Foo
{

	private function foo()
	{
		$this->protectedMethodFromChild();
	}

	protected function bar()
	{

	}

	public function ipsum()
	{

	}

	public function test($bar)
	{

	}

}

class Bar extends Foo
{

	private function foobar()
	{

	}

	public function lorem()
	{
		$this->loremipsum(); // nonexistent
		$this->foo(); // private from an ancestor
		$this->bar();
		$this->ipsum();
		$this->foobar();
		$this->lorem();
		$this->test(); // missing parameter

		$string = 'foo';
		$string->method();
	}

	public function dolor($foo, $bar, $baz)
	{
		// fixing PHP bug #71416
		$methodReflection = new \ReflectionMethod(Bar::class, 'dolor');
		$methodReflection->invoke(new self());
		$methodReflection->invoke(new self(), 'foo', 'bar', 'baz');
	}

	protected function protectedMethodFromChild()
	{
		$foo = new UnknownClass();
		$foo->doFoo();
	}

}
