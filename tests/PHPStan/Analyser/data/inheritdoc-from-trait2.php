<?php

namespace InheritDocFromTrait2;

class Foo extends FooParent
{

	/**
	 * {@inheritdoc}
	 */
	public function doFoo($string)
	{
		die;
	}

}
