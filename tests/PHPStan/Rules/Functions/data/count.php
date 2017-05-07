<?php

namespace CountFunction;

class Foo
{

	public function doFoo(array $items)
	{
		count($items);
	}

	/**
	 * @param BarCountable $countableCollection
	 */
	public function doBar($countableCollection)
	{
		count($countableCollection);
	}

	public function doBaz(string $foo)
	{
		count($foo);
	}

	/**
	 * @param mixed[]|BarCountable
	 */
	public function doLorem($union)
	{
		count($union);
	}

	public function doIpsum(self $self)
	{
		count($self);
	}

	/**
	 * @param BarCountable|self $unionWithUncountable
	 */
	public function doDolor($unionWithUncountable)
	{
		count($unionWithUncountable);
	}

}

class BarCountable implements \Countable
{

	public function count(): int
	{

	}

}
