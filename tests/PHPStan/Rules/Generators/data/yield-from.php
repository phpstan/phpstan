<?php

namespace YieldFromCheck;

use DateTimeImmutable;

class Foo
{

	/**
	 * @return \Generator<DateTimeImmutable, string>
	 */
	public function doFoo(): \Generator
	{
		yield from 1;
		yield from $this->doBar();
	}

	/**
	 * @return \Generator<\stdClass, int>
	 */
	public function doBar()
	{
		$stdClass = new \stdClass();
		yield $stdClass => 1;
	}

	public function doBaz()
	{
		$stdClass = new \stdClass();
		yield $stdClass => 1;
	}

}
