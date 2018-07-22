<?php

namespace IssetNamespace;

class Foo
{

	/**
	 * @param int[] $integers
	 */
	public function doFoo(array $integers, string $string, $mixedIsset, $mixedArrayKeyExists)
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

		$arrayCopy = $array;
		$anotherArrayCopy = $array;
		$yetAnotherArrayCopy = $array;

		if (!isset($array['b'])) {
			return;
		}

		if (!array_key_exists('b', $arrayCopy)) {
			return;
		}
		if (array_key_exists('b', $anotherArrayCopy)) {
			return;
		}
		if (array_key_exists($string, $yetAnotherArrayCopy)) {
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

		if (!isset($mixedIsset['a'])) {
			return;
		}

		if (!array_key_exists('a', $mixedArrayKeyExists)) {
			return;
		}

		foreach ($itemsA as $a) {
			die;
		}
	}

}
