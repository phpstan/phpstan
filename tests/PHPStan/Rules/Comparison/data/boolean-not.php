<?php

namespace ConstantCondition;

class BooleanNot
{

	public function doFoo(int $i, \stdClass $std)
	{
		if (!$i) {

		}
		if (!$std) {

		}

		$zero = 0;
		if (!$zero) {

		}
	}

	/**
	 * @param int $i
	 * @param \stdClass $std
	 * @param Foo|Bar $union
	 * @param Lorem&Ipsum $intersection
	 */
	public function elseifs(int $i, \stdClass $std, $union, $intersection)
	{
		if ($i) {

		} elseif (!$std) {

		}
	}

	public function ternary(\stdClass $std)
	{
		!$std ? 'foo' : 'bar';
	}

}
