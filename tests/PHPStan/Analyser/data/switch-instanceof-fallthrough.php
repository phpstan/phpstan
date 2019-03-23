<?php

namespace SwitchInstanceOfFallthrough;

class Foo
{

	/**
	 * @param object $object
	 */
	public function doFoo($object)
	{
		switch (true) {
			case $object instanceof A:
			case $object instanceof B:
				die;
		}
	}

}
