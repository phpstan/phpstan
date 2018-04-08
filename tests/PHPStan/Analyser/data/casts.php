<?php

namespace TypesNamespaceCasts;

class Foo
{

	public function doFoo(string $str)
	{
		$castedInteger = (int) foo();
		$castedBoolean = (bool) foo();
		$castedFloat = (float) foo();
		$castedString = (string) foo();
		$castedArray = (array) foo();
		$castedObject = (object) foo();
		$foo = new self();
		$castedFoo = (object) $foo;

		/** @var self|array $arrayOrObject */
		$arrayOrObject = foo();
		$castedArrayOrObject = (object) $arrayOrObject;

		/** @var bool $bool */
		$bool = doFoo();
		die;
	}

}
