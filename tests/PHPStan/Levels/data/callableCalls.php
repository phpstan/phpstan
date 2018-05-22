<?php

namespace Levels\CallableCalls;

class Foo
{

	/**
	 * @param int|string $a
	 * @param float|string $b
	 * @param int $c
	 * @param int|float $d
	 */
	public function doFoo(
		$a,
		$b,
		$c,
		$d
	)
	{
		$a();
		$b();
		$c();
		$d();
		$f = function (int $i) {

		};
		$f(1);
		$f(1.1);
		$f($a);
		$f($b);

		if (rand(0, 1)) {
			$f = null;
		}

		$f();
		$f(1);
	}

}
