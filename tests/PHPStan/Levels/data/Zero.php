<?php declare(strict_types = 1);

namespace Levels;

class Zero
{

	public function doFoo(int $i)
	{
		$this->doFoo();

		$self = new self();
		$this->doFoo();
	}

}
