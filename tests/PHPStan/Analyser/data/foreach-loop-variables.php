<?php

namespace LoopVariables;

class ForeachFoo
{

	/** @var int[] */
	private $property = [];

	public function doFoo(string $s)
	{
		$foo = null;
		$key = null;
		$val = null;
		$nullableVal = null;

		$this->property = [];

		$integers = [];
		$i = 0;
		$iterableArray = [];
		if (rand(0, 1) === 0) {
			$iterableArray = [1, 2, 3];
		}
		$falseOrObject = false;
		foreach ($iterableArray as $key => $val) {
			'begin';
			$foo = new Foo();
			'afterAssign';

			if ($nullableVal === null) {
				'nullableValIf';
				$nullableVal = 1;
			} else {
				$nullableVal *= 10;
				'nullableValElse';
			}

			if ($falseOrObject === false) {
				$falseOrObject = new Foo();
			}

			$foo && $i++;

			$nullableInt = $val;
			if (rand(0, 1) === 1) {
				$nullableInt = null;
			}

			if (something()) {
				$foo = new Bar();
				break;
			}
			if (something()) {
				$foo = new Baz();
				return;
			}
			if (something()) {
				$foo = new Lorem();
				continue;
			}

			if ($nullableInt === null) {
				continue;
			}

			if (isset($this->property[$s])) {
				continue;
			}

			$this->property[$s] = $val;

			$integers[] = $nullableInt;

			'end';
		}

		$emptyForeachKey = null;
		$emptyForeachVal = null;
		foreach ($iterableArray as $emptyForeachKey => $emptyForeachVal) {

		}

		'afterLoop';
	}

}
