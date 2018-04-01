<?php

namespace TraitsReturnThis;

class Bar extends Foo
{

	public function doFoo()
	{
		(new Foo())->returnsThisWithSelf()->doFoo();
		(new Foo())->returnsThisWithFoo()->doFoo();
		(new Bar())->returnsThisWithSelf()->doFoo();
		(new Bar())->returnsThisWithFoo()->doFoo();
	}

}
