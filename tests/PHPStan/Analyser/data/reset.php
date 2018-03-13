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
		die;
	}

}
