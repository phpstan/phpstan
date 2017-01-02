<?php

namespace CallInstanceofMethodsTests;

interface Foo
{
	public function do();
}
abstract class Baz
{
	public abstract function doBaz();
}
abstract class BazChild extends Baz implements Foo
{
}
abstract class Test
{
	public function testMethod(Baz $baz)
	{
		if (!$baz instanceof Foo) {
			return;
		}

		$baz->do();
		$baz->doBaz();
	}
}

