<?php

namespace CheckTypeFunctionCall;

class Foo
{

	/**
	 * @param int $integer
	 * @param int|string $integerOrString
	 * @param string $string
	 * @param callable $callable
	 * @param array $array
	 * @param array<int> $arrayOfInt
	 */
	public function doFoo(
		int $integer,
		$integerOrString,
		string $string,
		callable $callable,
		array $array,
		array $arrayOfInt
	)
	{
		if (is_int($integer)) { // always true

		}
		if (is_int($integerOrString)) { // fine

		}
		if (is_int($string)) { // always false

		}
		$className = 'Foo';
		if (is_a($className, \Throwable::class, true)) { // should be fine

		}
		if (is_array($callable)) {

		}
		if (is_callable($array)) {

		}
		if (is_callable($arrayOfInt)) {

		}
	}

}

class TypeCheckInSwitch
{

	public function doFoo($value)
	{
		switch (true) {
			case is_int($value):
			case is_float($value):
				break;
		}
	}

}

class StringIsNotAlwaysCallable
{

	public function doFoo(string $s)
	{
		if (is_callable($s)) {
			$s();
		}
	}

}

class CheckIsCallable
{

	public function test()
	{
		if (is_callable('date')) {

		}
		if (is_callable('nonexistentFunction')) {

		}
	}

}
