<?php // lint >= 7.1

namespace ArrayAccesable;

class Foo
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

}
