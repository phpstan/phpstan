<?php

namespace ConstantCondition;

class Foo
{

}

class Bar
{

}

interface Lorem
{

}

interface Ipsum
{

}

class IfCondition
{

	/**
	 * @param int $i
	 * @param \stdClass $std
	 * @param Foo|Bar $union
	 * @param Lorem&Ipsum $intersection
	 */
	public function doFoo(int $i, \stdClass $std, $union, $intersection)
	{
		if ($i) {

		}

		if ($std) {

		}

		$zero = 0;
		if ($zero) {

		}

		if ($union instanceof Foo || $union instanceof Bar) {

		}

		if ($union instanceof Foo && $union instanceof Bar) {

		}

		if ($intersection instanceof Lorem && $intersection instanceof Ipsum) {

		}

		if ($intersection instanceof Lorem || $intersection instanceof Ipsum) {

		}
	}

}
