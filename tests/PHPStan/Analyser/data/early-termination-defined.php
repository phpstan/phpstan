<?php

namespace EarlyTermination;

class Foo
{

	public function doFoo()
	{
		throw new \Exception();
	}

}

class Bar extends Foo
{

}
