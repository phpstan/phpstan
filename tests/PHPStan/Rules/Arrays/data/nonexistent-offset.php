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

}
