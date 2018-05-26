<?php declare(strict_types = 1);

namespace HasTraitUse;

trait FooTrait
{

	public function variadicMethod()
	{
		if (doFoo()) {
			$args = func_get_args();
		}
	}

}
