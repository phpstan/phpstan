<?php

namespace Levels\AcceptTypes;

class Foo
{

	/**
	 * @param int $i
	 * @param float $j
	 * @param float|string $k
	 * @param int|null $l
	 * @param int|float $m
	 */
	public function doFoo(
		int $i,
		float $j,
		$k,
		?int $l,
		$m
	)
	{
		$this->doBar();
		$this->doBar($i);
		$this->doBar($j);
		$this->doBar($k);
		$this->doBar($l);
		$this->doBar($m);
	}

	public function doBar(int $i)
	{

	}

}
