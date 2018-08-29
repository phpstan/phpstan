<?php

namespace AppendedArrayItemToString;

class Alpha
{

	/** @var string */
	private $string;

	public function assign()
	{
		$this->string = '';
		$this->string['key'] = 'value';
		$this->string[0] = 'value';
	}
}
