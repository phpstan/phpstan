<?php

namespace IssetNamespace;

class Foo
{

	/**
	 * @param int[] $integers
	 */
	public function doFoo(array $integers, string $string)
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

		$nullableArray = ['a' => null, 'b' => null, 'c' => null];
		if (rand(0, 1) === 1) {
			$nullableArray['a'] = 'foo';
			$nullableArray['b'] = 'bar';
			$nullableArray['c'] = 'baz';
		}

		if (!isset($nullableArray['b'])) {
			return;
		}

		if (!array_key_exists('c', $nullableArray)) {
			return;
		}

		foreach ($itemsA as $a) {
			die;
		}
	}

}
