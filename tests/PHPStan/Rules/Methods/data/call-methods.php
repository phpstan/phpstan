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

		$this->returnsVoid();
		$this->dolor($this->returnsVoid(), 'bar', 'baz');

		foreach ($this->returnsVoid() as $void) {
			$this->returnsVoid();
			if ($this->returnsVoid()) {
				$this->returnsVoid();
				$this->returnsVoid() ? 'foo' : 'bar';
				doFoo() ? $this->returnsVoid() : 'bar';
				doFoo() ? 'foo' : $this->returnsVoid();
				$void = $this->returnsVoid();
				$void = $this->returnsVoid() ? 'foo' : 'bar';
				$void = doFoo() ? $this->returnsVoid() : 'bar';
				$void = doFoo() ? 'foo' : $this->returnsVoid();
				$this->returnsVoid() && 'foo';
				'foo' && $this->returnsVoid();
				$this->returnsVoid() || 'foo';
				'foo' || $this->returnsVoid();
				$void = $this->returnsVoid() && 'foo';
				$void = 'foo' && $this->returnsVoid();
				$void = $this->returnsVoid() || 'foo';
				$void = 'foo' || $this->returnsVoid();
			}
		}

		switch ($this->returnsVoid()) {
			case $this->returnsVoid():
				$this->returnsVoid();
		}
	}

	/**
	 * @return void
	 */
	private function returnsVoid()
	{

	}

}

$f = function () {
	/** @var \stdClass[]|\ArrayObject $arrayOfStdClass */
	$arrayOfStdClass = new \ArrayObject([new \stdClass()]);
	$arrayOfStdClass->doFoo();
};

$g = function () {
	$pdo = new \PDO('dsn', 'username', 'password');
	$pdo->query();
	$pdo->query('statement');
};

class ClassWithToString
{

	public function __toString()
	{
		return 'foo';
	}

	public function acceptsString(string $foo)
	{

	}

}

function () {
	$foo = new ClassWithToString();
	$foo->acceptsString($foo);

	$closure = function () {

	};
	$closure->__invoke(1, 2, 3);
};
