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

}

class FooChild extends Foo
{

}
