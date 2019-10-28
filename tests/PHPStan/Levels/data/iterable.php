<?php

namespace Levels\Iterables;

class Foo
{

	/**
	 * @param mixed[] $array
	 * @param mixed[]|null $arrayOrNull
	 * @param int $int
	 * @param int|float $intOrFloat
	 * @param mixed[]|false $arrayOrFalse
	 */
	public function doFoo(
		array $array,
		?array $arrayOrNull,
		int $int,
		$intOrFloat,
		$arrayOrFalse
	)
	{
		foreach ($array as $val) {

		}
		foreach ($arrayOrNull as $val) {

		}
		foreach ($int as $val) {

		}
		foreach ($intOrFloat as $val) {

		}
		foreach ($arrayOrFalse as $val) {

		}
	}

}
