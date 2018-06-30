<?php

namespace Levels\ArrayDimFetches;

class Foo
{

	public function doFoo(\stdClass $stdClass, ?array $arrayOrNull)
	{
		echo $stdClass[1];
		echo $arrayOrNull[0];

		$arr = [
			'a' => 1,
		];

		echo $arr['b'];

		if (rand(0, 1)) {
			$arr = $stdClass;
		}

		echo $arr['a'];
		echo $arr['b'];
	}

	public function doBar()
	{
		$arr = [
			'a' => 1,
		];
		if (rand(0, 1)) {
			$arr['b'] = 1;
		}

		echo $arr['b'];
	}

}
