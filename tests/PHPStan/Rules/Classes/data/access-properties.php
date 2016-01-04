<?php

namespace TestAccessProperties;

class FooAccessProperties
{

	private $foo;

	protected $bar;

	public $ipsum;

}

class BarAccessProperties extends FooAccessProperties
{

	private $foobar;

	public function foo()
	{
		$this->loremipsum; // nonexistent
		$this->foo; // private from an ancestor
		$this->bar;
		$this->ipsum;
		$this->foobar;
		Foo::class;
	}

}
