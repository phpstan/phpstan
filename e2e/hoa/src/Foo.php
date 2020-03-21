<?php

namespace Hoa;

use Hoa\File\Read;

class Foo extends Read
{

	public function doFoo(): void
	{
		parent::open();
	}

}
