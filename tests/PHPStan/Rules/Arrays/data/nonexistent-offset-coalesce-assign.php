<?php // lint >= 7.4

namespace NonexistentOffsetCoalesceAssign;

class Foo
{

	public function doFoo()
	{
		$a = [];
		$a['foo'] ??= 'foo';
	}

	public function doBar()
	{
		$a = [];
		if (rand(0, 1)) {
			$a['foo'] = 'foo';
		}
		$a['foo'] ??= 'foo';
	}

	public function doBaz()
	{
		$a = ['foo' => 'foo'];
		$a['foo'] ??= 'bar';
	}

}
