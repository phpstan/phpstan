<?php

namespace Levels\Comparison;

class Foo
{

	/**
     * @param \stdClass $object
	 * @param int $int
     * @param float $float
	 * @param string $string
	 * @param int|string $intOrString
	 * @param int|\stdClass $intOrObject
	 */
	public function doFoo(
	    \stdClass $object,
		int $int,
		float $float,
		string $string,
		$intOrString,
        $intOrObject
	)
	{
        $object == $int;
        $object == $float;
        $object == $string;
        $object == $intOrString;
        $object == $intOrObject;
	}

}
