<?php

declare(strict_types=1);

namespace Levels\Echo_;

class Foo
{
	/**
	 * @param array $array
	 * @param array|callable $arrayOrCallable
	 * @param array|float|int $arrayOrFloatOrInt
	 * @param array|string $arrayOrString
	 */
	public function doFoo(
		array $array,
		$arrayOrCallable,
		$arrayOrFloatOrInt,
		$arrayOrString
	): void {
		echo $array, $arrayOrCallable, $arrayOrFloatOrInt, $arrayOrString;
	}
}