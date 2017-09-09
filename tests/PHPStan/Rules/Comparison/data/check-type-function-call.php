<?php

namespace CheckTypeFunctionCall;

class Foo
{

	/**
	 * @param int $integer
	 * @param int|string $integerOrString
	 * @param string $string
	 */
	public function doFoo(
		int $integer,
		$integerOrString,
		string $string
	)
	{
		if (is_int($integer)) { // always true

		}
		if (is_int($integerOrString)) { // fine

		}
		if (is_int($string)) { // always false

		}
	}

}
