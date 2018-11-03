<?php

namespace Levels\Unreachable;

class Foo
{

	public function doStrictComparison()
	{
		$a = 5;
		if ($a === 5) {

		} else {

		}
	}

	public function doInstanceOf()
	{
		if ($this instanceof self) {

		} else {

		}
	}

	public function doTypeSpecifyingFunction(string $s)
	{
		if (is_string($s)) {

		} else {

		}
	}

	public function doOtherFunction()
	{
		if (print_r('foo')) {

		} else {

		}
	}

	public function doOtherValue()
	{
		if (true) {

		} else {

		}
	}

	public function doBooleanAnd()
	{
		$foo = 1;
		$bar = 2;

		if ($foo && $bar) {

		} else {

		}
	}

}

class Bar
{

	public function doStrictComparison()
	{
		$a = 5;
		$a === 5 ? 'foo' : 'bar';
	}

	public function doInstanceOf()
	{
		$this instanceof self ? 'foo' : 'bar';
	}

	public function doTypeSpecifyingFunction(string $s)
	{
		is_string($s) ? 'foo' : 'bar';
	}

	public function doOtherFunction()
	{
		print_r('foo') ? 'foo' : 'bar';
	}

	public function doOtherValue()
	{
		true ? 'foo' : 'bar';
	}

	public function doBooleanAnd()
	{
		$foo = 1;
		$bar = 2;

		$foo && $bar ? 'foo' : 'bar';
	}

}
