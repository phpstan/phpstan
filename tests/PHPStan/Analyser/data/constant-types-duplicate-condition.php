<?php

namespace DuplicateConditionNeverError;

class Foo
{

	public function doFoo(int $a, int $b)
	{
		$c = 0;

		if ($c === $a && $c === $b) {
			'inCondition';
			return +1;
		}

		'afterFirst';

		if ($c === $a) {
			return -1;
		}

		'afterSecond';

		if ($c === $b) {
			return +1;
		}

		'afterThird';
	}

}
