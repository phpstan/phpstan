<?php

namespace AccessPropertiesAfterIsNull;

class Foo
{

	/** @var self */
	private $fooProperty;

	public function doFoo()
	{
		$foo = new self();
		if (is_null($this->fooProperty) && $this->fooProperty->fooProperty) {

		}
		if (is_null($this->fooProperty) || $this->fooProperty->fooProperty) {

		}
		if (!is_null($this->fooProperty) && $this->fooProperty->fooProperty) {

		}
		if (!is_null($this->fooProperty) || $this->fooProperty->fooProperty) {

		}
		if (is_null($this->fooProperty) || $this->fooProperty->barProperty) {

		}
		if (!is_null($this->fooProperty) && $this->fooProperty->barProperty) {

		}

		while (is_null($this->fooProperty) && $this->fooProperty->fooProperty) {

		}
		while (is_null($this->fooProperty) || $this->fooProperty->fooProperty) {

		}
		while (!is_null($this->fooProperty) && $this->fooProperty->fooProperty) {

		}
		while (!is_null($this->fooProperty) || $this->fooProperty->fooProperty) {

		}
		while (is_null($this->fooProperty) || $this->fooProperty->barProperty) {

		}
		while (!is_null($this->fooProperty) && $this->fooProperty->barProperty) {

		}
	}

}
