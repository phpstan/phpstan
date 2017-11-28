<?php

namespace ForeachWithComplexValueType;

class Foo
{

	/**
	 * @param (float|self)[] $list
	 */
	public function doFoo(array $list)
	{
		foreach ($list as $value) {
			die;
		}
	}

}
