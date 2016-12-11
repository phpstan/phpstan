<?php declare(strict_types = 1);

namespace AnalyseTraits;

trait NestedFooTrait
{

	use FooTrait;

	public function doNestedTraitFoo()
	{
		$this->doNestedFoo();
	}

}
