<?php

namespace IncorrectMethodCase;

class Foo
{

	public function createImagick()
	{
		$imagick = new \Imagick();
		$imagick->readImageBlob('');
	}

	public function fooBar()
	{
		$this->foobar();
		$this->fooBar();
	}

}
