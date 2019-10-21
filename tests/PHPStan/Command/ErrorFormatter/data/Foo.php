<?php

namespace BaselineIntegration;

class Foo
{

	use FooTrait;

	public function doFoo(): int
	{
		return 'string';
	}

}
