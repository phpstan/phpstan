<?php declare(strict_types = 1);

namespace AnalyseTraits;

class SomeClass
{

	use FooTrait;

}



trait FooTrait
{

	public function doTraitFoo()
	{
		$this->doFoo();
	}



	public function conflictingMethodWithDifferentArgumentNames(string $string)
	{
		strpos($string, 'foo');
	}

}
