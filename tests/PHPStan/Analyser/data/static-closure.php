<?php

namespace StaticClosure;

class Foo
{

	public function doFoo(): void
	{
		static function () {
			die;
		};
	}

}
