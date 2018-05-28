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
		$this->doBar($i);
		$this->doBar($j);
		$this->doBar($k);
		$this->doBar($l);
		$this->doBar($m);
	}

	public function doBar(int $i)
	{

	}

	/**
	 * @param float|string $a
	 * @param float|string|null $b
	 * @param int $c
	 * @param int|null $d
	 */
	public function doBaz(
		$a,
		$b,
		int $c,
		?int $d
	)
	{
		$this->doLorem($a);
		$this->doLorem($b);
		$this->doLorem($c);
		$this->doLorem($d);
		$this->doIpsum($a);
		$this->doIpsum($b);
		$this->doBar(null);
	}

	/**
	 * @param int|callable $a
	 */
	public function doLorem($a)
	{

	}

	/**
	 * @param float $a
	 */
	public function doIpsum($a)
	{

	}

}
