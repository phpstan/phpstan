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
	}

}
