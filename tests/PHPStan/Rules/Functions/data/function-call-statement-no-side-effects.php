<?php

namespace FunctionCallStatementNoSideEffects;

class Foo
{

	public function doFoo()
	{
		printf('%s', 'test');
		sprintf('%s', 'test');
	}

}
