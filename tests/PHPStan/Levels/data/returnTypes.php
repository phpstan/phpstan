<?php

namespace Levels\ReturnTypes;

class Foo
{

	/**
	 * @param int $i
	 * @param float $j
	 * @param int|string $k
	 * @param float|string $l
	 * @param int|null $m
	 * @return int
	 */
	public function doFoo(
		int $i,
		float $j,
		$k,
		$l,
		?int $m
	)
	{
		if (rand(0, 1)) {
			return $i;
		}
		if (rand(0, 1)) {
			return $j;
		}
		if (rand(0, 1)) {
			return $k;
		}
		if (rand(0, 1)) {
			return $l;
		}
		if (rand(0, 1)) {
			return $m;
		}
		if (rand(0, 1)) {
			return;
		}
	}

	/**
	 * @param int $i
	 * @param float $j
	 * @param int|string $k
	 * @param float|string $l
	 * @param int|null $m
	 * @return void
	 */
	public function doBar(
		int $i,
		float $j,
		$k,
		$l,
		?int $m
	)
	{
		if (rand(0, 1)) {
			return $i;
		}
		if (rand(0, 1)) {
			return $j;
		}
		if (rand(0, 1)) {
			return $k;
		}
		if (rand(0, 1)) {
			return $l;
		}
		if (rand(0, 1)) {
			return $m;
		}
		if (rand(0, 1)) {
			return;
		}
	}

	/**
	 * @param array<string, bool|int|string|null> $array
	 * @return string[]|null
	 */
	public function returnArrayOrNull(
		$array
	): ?array
	{
		return $array;
	}

}
