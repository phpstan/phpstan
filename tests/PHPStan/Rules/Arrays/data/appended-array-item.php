<?php

namespace AppendedArrayItem;

// inferred from literal array
$xx = [1, 2, 3];
$xx[] = 4;
$xx['foo'] = 5;

$xx[] = 'foo';
$xx['bar'] = 'baz';

class Foo
{

	/**
	 * @param int[] $integers
	 * @param callable[] $callables
	 */
	public function doFoo(
		array $integers,
		array $callables
	)
	{
		$integers[] = 4;
		$integers['foo'] = 5;

		$integers[] = 'foo';
		$integers['bar'] = 'baz'; // already mixed[] here

		$callables[] = [$this, 'doFoo'];
		$callables[] = [1, 2, 3];

		/** @var callable[] $otherCallables */
		$otherCallables = $callables;
		$otherCallables[] = ['Closure', 'bind'];

		/** @var callable[] $anotherCallables */
		$anotherCallables = $callables;
		$anotherCallables[] = 'strpos';

		/** @var callable[] $yetAnotherCallables */
		$yetAnotherCallables = $callables;
		$yetAnotherCallables[] = [__CLASS__, 'classMethod'];

		$mappedStringArray = array_map(function ($item): string {
			return 'foo';
		}, []);
		$mappedStringArray[] = 1;

		/** @var callable[] $yetAnotherAnotherCallables */
		$yetAnotherAnotherCallables = $callables;
		$world = 'world';
		$yetAnotherAnotherCallables[] = ['Foo', "Hello $world"];

		$implicitlyCreatedArray['foo'] = ['bar'];
		$implicitlyCreatedArray['bar'] = 'baz';
	}

	public static function classMethod()
	{
	}

	/**
	 * @param int[] $integers
	 */
	public function assignOp(array $integers)
	{
		$integers[0] .= 'foo';
	}

}
