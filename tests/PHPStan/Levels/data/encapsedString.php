<?php declare(strict_types=1);

namespace Levels\EncapsedString;

class Foo
{

	/**
	 * @param mixed[] $array
	 * @param mixed[]|callable $arrayOrCallable
	 * @param mixed[]|float|int $arrayOrFloatOrInt
	 * @param mixed[]|string $arrayOrString
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
