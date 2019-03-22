<?php

namespace Levels\Casts;

class Foo
{

	/**
	 * @param array $array
	 * @param array|callable $arrayOrCallable
	 * @param array|float|int $arrayOrFloatOrInt
	 */
	public function doFoo(
		array $array,
		$arrayOrCallable,
		$arrayOrFloatOrInt
	)
	{
		$test = (int) $array;
		$test = (int) $arrayOrCallable;
		$test = (string) $arrayOrFloatOrInt;
	}

}
