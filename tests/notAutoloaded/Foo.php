<?php declare(strict_types = 1);

namespace PHPStan\Tests;

class Foo
{

	/** @var string */
	private $fooProperty;

	public function doFoo(): string
	{
		$this->fooProperty = 'test';

		return $this->fooProperty;
	}

}
