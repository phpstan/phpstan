<?php

class ExtendsClassWithUnknownPropertyType extends ClassWithUnknownPropertyType
{

	/** @var self */
	private $foo;

	public function doFoo()
	{
		$this->foo->foo();
	}

}
