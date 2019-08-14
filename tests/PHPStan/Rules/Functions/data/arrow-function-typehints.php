<?php

namespace ArrowFunctionExistingClassesInTypehints;

class Foo
{

	public function doFoo()
	{
		fn(Bar $bar): Baz => new Baz();
	}

}
