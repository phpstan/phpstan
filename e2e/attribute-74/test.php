<?php declare(strict_types = 1);

class MyDateTime extends DateTime
{
	/**
	 * @return MyDateTime|false
	 */
	#[ReturnTypeWillChange]
	public function modify($modifier)
	{
		return false;
	}
}
