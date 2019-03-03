<?php

namespace CallFunctions;

class Foo
{

	public function doFoo()
	{
		$string = '';
		$arr=[];
		foreach($arr as $item) {
			$string = preg_replace('~test~','',$string);
			assert(is_string($string));
		}
	}

}
