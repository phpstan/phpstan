<?php declare(strict_types = 1);

namespace TraitErrors;

trait MyTrait
{
	public function test()
	{
		echo $undefined;
		$this->undefined($undefined);
	}
}


class MyClass
{
	use MyTrait;
}
