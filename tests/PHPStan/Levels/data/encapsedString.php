<?php declare(strict_types=1);

namespace Levels\EncapsedString;

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
	): void
	{
		$this->takesString("foo $array");
		$this->takesString("foo $arrayOrCallable");
		$this->takesString("foo $arrayOrFloatOrInt");
		$this->takesString("foo $arrayOrString");
	}

	public function takesString(string $str): void
	{

	}

}
