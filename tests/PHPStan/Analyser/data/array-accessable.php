<?php

namespace ArrayAccesable;

class Foo implements \ArrayAccess
{

	public function __construct()
	{
		die;
	}

	/**
	 * @return string[]
	 */
	public function returnArrayOfStrings(): array
	{
	}

	/**
	 * @return \ArrayObject|string[]
	 */
	public function returnArrayObjectOfStrings(): \ArrayObject
	{

	}

	/**
	 * @return mixed
	 */
	public function returnMixed()
	{

	}

	/**
	 * @return \Traversable|string[]
	 */
	public function returnTraversableOnlyStrings(): \Traversable
	{

	}

	public function offsetExists($offset)
	{

	}

	public function offsetGet($offset): int
	{

	}

	public function offsetSet($offset, $value)
	{

	}

	public function offsetUnset($offset)
	{

	}

}
