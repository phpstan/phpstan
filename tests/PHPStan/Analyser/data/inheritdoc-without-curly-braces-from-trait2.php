<?php

namespace InheritDocWithoutCurlyBracesFromTrait2;

class Foo extends FooParent
{

	/**
	 * @inheritdoc
	 */
	public function doFoo($string)
	{
		die;
	}

}
