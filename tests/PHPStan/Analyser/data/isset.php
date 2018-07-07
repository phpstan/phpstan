<?php

namespace IssetNamespace;

class Foo
{

	/**
	 * @param int[] $integers
	 */
	public function doFoo(array $integers)
	{
		if (rand(0, 1) === 0) {
			$array = [
				'a' => 1,
				'b' => 2,
			];
		} elseif (rand(0, 1) === 0) {
			$array = [
				'a' => 2,
			];
		} elseif (rand(0, 1) === 0) {
			$array = [
				'a' => 3,
				'b' => 3,
				'c' => 4,
			];
		} else {
			$array = [
				'a' => 3,
				'b' => null,
			];
		}

		if (!isset($array['b'])) {
			return;
		}

		if (!isset($integers['a'])) {
			return;
		}

		$itemsA = [
			'foo',
			'derp',
			'herp'
		];

		$itemsB = [
			'foo',
			'bar',
			'baz',
		];

		$lookup = array_fill_keys($itemsB, true);

		foreach ($itemsA as $a) {
			die;
		}
	}

}
