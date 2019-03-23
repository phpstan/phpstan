<?php

namespace YieldTypeRuleTest;

class Foo
{

	/**
	 * @return \Generator<string, int>
	 */
	public function doFoo(): \Generator
	{
		yield 'foo' => 1;
		yield 'foo' => 'bar';
		yield;
		yield 1;
		yield 'foo';
	}

}
