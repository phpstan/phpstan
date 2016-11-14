<?php

namespace ReturnTypes;

class Foo implements FooInterface
{

	public function returnNothing()
	{
		return;
	}

	public function returnInteger(): int
	{
		return 1;
		return;
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

}

class FooChild extends Foo
{

}

interface FooInterface
{

}

class OtherInterfaceImpl implements FooInterface
{

}
