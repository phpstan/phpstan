<?php // lint >= 7.4

namespace ArraySpreadOperator;

class Foo
{

	/**
	 * @param int[] $integersArray
	 * @param int[] $integersIterable
	 */
	public function doFoo(
		array $integersArray,
		iterable $integersIterable
	)
	{
		$integersOne = [1, 2, 3, ...$integersArray];
		$integersTwo = [1, 2, 3, ...$integersIterable];
		$integersThree = [1, 2, ...[3, 4, 5], 6, 7];

		$integersFour = array(1, 2, 3, ...$integersArray);
		$integersFive = array(1, 2, 3, ...$integersIterable);
		$integersSix = array(1, 2, ...[3, 4, 5], 6, 7);

		$integersSeven = array(1, 2, ...array(3, 4, 5), 6, 7);
		die;
	}

}
