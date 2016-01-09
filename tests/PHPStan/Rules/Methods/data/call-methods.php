<?php

namespace Test;

class Foo
{

	private function foo()
	{

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
	}

}
