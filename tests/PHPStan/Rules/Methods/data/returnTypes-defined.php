<?php

namespace ReturnTypes;

class FooParent
{

	/**
	 * @return static
	 */
	public function returnStatic(): self
	{
		return $this;
	}

	/**
	 * @return int
	 */
	public function returnIntFromParent()
	{
		return 1;
	}

	/**
	 * @return void
	 */
	public function returnsVoid()
	{

	}

}

interface FooInterface
{

}

class OtherInterfaceImpl implements FooInterface
{

}
