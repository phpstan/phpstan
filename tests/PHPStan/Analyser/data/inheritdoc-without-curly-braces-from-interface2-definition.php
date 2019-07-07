<?php

namespace InheritDocWithoutCurlyBracesFromInterface2;

interface FooInterface extends BarInterface
{
}

interface BarInterface
{

	/**
	 * @param int $int
	 */
	public function doBar($int);

}
