<?php

namespace CheckTypeFunctionCall;

class Foo
{

	/**
	 * @param int $integer
	 * @param int|string $integerOrString
	 * @param string $string
	 * @param callable $callable
	 */
	public function doFoo(
		int $integer,
		$integerOrString,
		string $string,
		callable $callable
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
	}

}
