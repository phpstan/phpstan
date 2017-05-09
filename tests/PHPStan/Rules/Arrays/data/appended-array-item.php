<?php

namespace AppendedArrayItem;

// inferred from literal array
$integers = [1, 2, 3];
$integers[] = 4;
$integers['foo'] = 5;

$integers[] = 'foo';
$integers['bar'] = 'baz';

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
		$otherCallables[] = [Foo::class, 'doFoo'];

		/** @var callable[] $anotherCallables */
		$anotherCallables = $callables;

		$anotherCallables[] = 'AppendedArrayItem\\doFoo';

		/** @var callable[] $yetAnotherCallables */
		$yetAnotherCallables = $callables;
		$yetAnotherCallables[] = [__CLASS__, 'classMethod'];

		$mappedStringArray = array_map(function ($item): string {
			return 'foo';
		}, []);
		$mappedStringArray[] = 1;
	}

	public static function classMethod()
	{

	}

}

function doFoo()
{

}
