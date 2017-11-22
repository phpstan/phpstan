<?php

namespace ForeachWithGenericsPhpDoc;

class Foo
{

	/**
	 * @param iterable<self|Bar, string|int|float> $list
	 */
	public function doFoo(array $list)
	{
		foreach ($list as $key => $value) {
			die;
		}
	}

}
