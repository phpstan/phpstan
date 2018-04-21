<?php

namespace PassedByReference;

class Foo
{

	public function doFoo()
	{
		$arr = [1, 2, 3];
		reset($arr);

		preg_match('a', 'b', $matches);

		$s = '';
		$this->doBar($s);

		die;
	}

	public function doBar(string &$s)
	{

	}

}
