<?php

namespace ConstantCondition;

class Ternary
{

	public function doFoo(int $i, \stdClass $std)
	{
		$i ? 'foo' : 'bar';
		$std ? 'foo' : 'bar';
		!$std ? 'foo' : 'bar';

		$zero = 0;
		$zero ? 'foo' : 'bar';

		$i ? true : true;
		$i ? true : null;
		$i ? $std : true;
		$i ? $std : false;
		$i ? $std : null;
		$i ? [] : [];
		$i ? [23, 24] : [23, 25];
		$i ? [23, 24] : [23, 24];
		$i ? [1 => 1] : ['1' => 1];
		$i ? [1 => 1] : ['asd' => 1];
		$i ?: $i;
		$i ? false : true;
	}

}
