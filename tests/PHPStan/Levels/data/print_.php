<?php

declare(strict_types=1);

namespace Levels\Print_;

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
		print $array;
		print $arrayOrCallable;
		print $arrayOrFloatOrInt;
		print $arrayOrString;
	}
}