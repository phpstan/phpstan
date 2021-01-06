<?php

namespace App;

class Foo extends \ThirdParty\Bar
{

	public function doFoo(): void
	{
		$this->doBar();
	}

}
