<?php declare(strict_types = 1);

namespace TraitErrors;

class TraitInEvalUse
{

	use \TraitInEval;

	public function doLorem()
	{
		$this->doFoo(1);
	}

}
