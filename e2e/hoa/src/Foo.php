<?php

namespace Hoa;

use Hoa\File\Read;

abstract class Foo extends Read
{

	public function doFoo(): void
	{
		parent::open();
	}

}
