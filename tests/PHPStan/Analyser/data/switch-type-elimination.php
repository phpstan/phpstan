<?php

namespace SwitchTypeElimination;

class Foo
{

	/**
	 * @param string|int $stringOrInt
	 */
	public function doFoo($stringOrInt)
	{
		switch (true) {
			case is_int($stringOrInt):
				break;
			case doFoo():
				die;
		}
	}

}
