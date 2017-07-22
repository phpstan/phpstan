<?php declare(strict_types=1);

/**
 * @property int | float $numericBazBazProperty
 *
 * @method void simpleMethod
 * @method string returningMethod()
 * @method ?float returningNullableScalar()
 * @method ?\stdClass returningNullableObject()
 * @method void complicatedParameters(string $a, ?int|?float|?\stdClass $b, \stdClass $c = null, string|?int $d)
 * @method Image rotate(float $angle, $backgroundColor)
 * @method int | float paramMultipleTypesWithExtraSpaces(string | null $string, stdClass | null $object)
 */
class Foo
{


	public function doSomething()
	{
		/** @var Bar */
		$number = $this;
	}

}
