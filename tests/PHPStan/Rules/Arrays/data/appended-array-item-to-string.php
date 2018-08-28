<?php

namespace AppendedArrayItemToString;

class Foo
{

	/** @var string */
	private $string;

	public function doFoo()
	{
		$this->string = '';
		$this->string['key'] = 'value';
	}
}
