<?php // lint >= 7.4

namespace UnpackIterable;

class Foo
{

	/**
	 * @param int[] $integers
	 * @param int[]|null $integersOrNull
	 */
	public function doFoo(
		array $integers,
		?array $integersOrNull,
		string $str
	)
	{
		$foo = [
			...[1, 2, 3],
			...$integers,
			...$integersOrNull,
			...2,
			...$str
		];
	}

}
