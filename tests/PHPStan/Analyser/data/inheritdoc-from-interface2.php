<?php

namespace InheritDocFromInterface2;

class Foo implements FooInterface
{

	/**
	 * {@inheritdoc}
	 */
	public function doBar($int)
	{
		die;
	}

}
