<?php

namespace NonexistentOffset;

class Foo
{

	public function nonexistentOffsetOnArray()
	{
		$array = [
			'a' => new \stdClass(),
			2,
		];

		echo $array['a'];
		echo $array[0];
		echo $array['b'];
		echo $array[1];
	}

	public function assigningNewKeyToLiteralArray()
	{
		$array = [];
		$array[] = 0;
		$array['aaa'] = 1;

		/** @var string $key */
		$key = doFoo();
		$array[$key] = 2;
	}

	public function assigningToNullable()
	{
		$null = null;
		$null[] = 'test';

		/** @var mixed[]|null $nullable */
		$nullable = doFoo();
		$nullable['test'] = 0;
		echo $nullable['testt'];
	}

	public function unsetOffset()
	{
		$array = [
			'a' => new \stdClass(),
			'b' => 1,
		];

		echo $array['a'];
		echo $array['b'];

		unset($array['a']);

		echo $array['a'];
		echo $array['b'];
	}

	public function arrayAfterForeaches()
	{
		$result = [
			'id' => 'blabla', // string
			'allowedRoomCounter' => 0,
			'roomCounter' => 0,
		];

		foreach ([1, 2] as $x) {
			$result['allowedRoomCounter'] += $x;
		}

		foreach ([3, 4] as $x) {
			$result['roomCounter'] += $x;
		}
	}

	public function errorType()
	{
		$array = [
			'foo' => NONEXISTENT_CONSTANT,
		];
		echo $array['foo'];
	}

	public function cumulative()
	{
		$arr = [1, 1, 1, 1, 2, 5, 3, 2];
		/** @var (string|int)[] */
		$cumulative = [];

		foreach ($arr as $val) {
			if (!isset($cumulative[$val])) {
				$cumulative[$val] = 0;
			}

			$cumulative[$val] = $cumulative[$val] + 1;
		}
	}

	public function classDoesNotExist(Bar $foo)
	{
		echo $foo['bar'];
		$foo[] = 'test';
	}

	/**
	 * @param array<string, string> $array
	 * @param int $i
	 */
	public function trickyArrayCasting(array $array, int $i)
	{
		echo $array[0];
		echo $array['0'];
		echo $array['foo'];
		echo $array[$i];
	}

	public function assigningToNull()
	{
		$null = null;
		$null['test'] = 'foo';
	}

	public function readingNull()
	{
		$null = null;
		echo $null['test'];
	}

	public $propertyOffsetAssignment;

	public function propertyOffsetAssignment()
	{
		$this->propertyOffsetAssignment = [];
		$this->propertyOffsetAssignment['foo'] = 1;
		echo $this->propertyOffsetAssignment['foo'];
	}

}
