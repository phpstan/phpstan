<?php

namespace ResetDynamicReturnTypeExtension;

class Foo
{

	/**
	 * @param \stdClass[] $generalArray
	 * @param mixed $somethingElse
	 */
	public function doFoo(array $generalArray, $somethingElse)
	{
		$emptyConstantArray = [];
		$constantArray = [
			'a' => 1,
			'b' => 2,
		];

		$conditionalArray = ['foo', 'bar'];
		if (doFoo()) {
			array_unshift($conditionalArray, 'baz');
		}
		die;
	}

}
