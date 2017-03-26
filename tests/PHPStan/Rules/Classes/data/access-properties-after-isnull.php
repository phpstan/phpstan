<?php

namespace AccessPropertiesAfterIsNull;

class Foo
{

	/** @var self */
	private $fooProperty;

	public function doFoo()
	{
		$foo = new self();
		if (is_null($foo) && $foo->fooProperty) {

		}
		if (is_null($foo) || $foo->fooProperty) {

		}
		if (!is_null($foo) && $foo->fooProperty) {

		}
		if (!is_null($foo) || $foo->fooProperty) {

		}
		if (is_null($foo) || $foo->barProperty) {

		}
		if (!is_null($foo) && $foo->barProperty) {

		}

		while (is_null($foo) && $foo->fooProperty) {

		}
		while (is_null($foo) || $foo->fooProperty) {

		}
		while (!is_null($foo) && $foo->fooProperty) {

		}
		while (!is_null($foo) || $foo->fooProperty) {

		}
		while (is_null($foo) || $foo->barProperty) {

		}
		while (!is_null($foo) && $foo->barProperty) {

		}
	}

}
