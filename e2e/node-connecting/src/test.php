<?php

namespace NodeConnecting;

class Foo
{

	public function doFoo(bool $b): void
	{
		if ($b) {

		}
		echo 'bar';
		do {
			break;
		} while ($b);
	}

}
