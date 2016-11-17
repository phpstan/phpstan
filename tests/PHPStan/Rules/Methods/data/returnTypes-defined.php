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

}

interface FooInterface
{

}

class OtherInterfaceImpl implements FooInterface
{

}
