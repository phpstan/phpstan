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
	 * @param mixed[]|BarCountable $union
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

	/**
	 * @param null $null
	 */
	public function doSit($null)
	{
		count($null);
	}

	/**
	 * @param int|null $nullable
	 */
	public function doAmet($nullable)
	{
		count($nullable);
	}

}

class BarCountable implements \Countable
{

	public function count(): int
	{

	}

}

class CountOnArrayKey
{

	public function test()
	{
		$whereArgs = ['', []];
		count($whereArgs[1]);
	}

}
