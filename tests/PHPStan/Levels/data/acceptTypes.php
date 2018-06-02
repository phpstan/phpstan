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

	/**
	 * @param int[] $i
	 * @param float[] $j
	 * @param (float|string)[] $k
	 * @param (int|null)[] $l
	 * @param (int|float)[] $m
	 */
	public function doFooArray(
		array $i,
		array $j,
		array $k,
		array $l,
		array $m
	)
	{
		$this->doBarArray($i);
		$this->doBarArray($j);
		$this->doBarArray($k);
		$this->doBarArray($l);
		$this->doBarArray($m);
	}

	/**
	 * @param int[] $i
	 */
	public function doBarArray(array $i)
	{

	}

	public function doBazArray()
	{
		$ints = [1, 2, 3];
		$floats = [1.1, 2.2, 3.3];
		$floatsAndStrings = [1.1, 2.2];
		$intsAndNulls = [1, 2, 3];
		$intsAndFloats = [1, 2, 3];
		if (rand(0, 1) === 1) {
			$floatsAndStrings[] = 'str';
			$intsAndNulls[] = null;
			$intsAndFloats[] = 1.1;
		}

		$this->doBarArray($ints);
		$this->doBarArray($floats);
		$this->doBarArray($floatsAndStrings);
		$this->doBarArray($intsAndNulls);
		$this->doBarArray($intsAndFloats);
	}

}
