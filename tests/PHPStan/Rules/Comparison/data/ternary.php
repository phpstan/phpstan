<?php

namespace ConstantCondition;

class Ternary
{

	public function doFoo(int $i, \stdClass $std)
	{
		$i ? 'foo' : 'bar';
		$std ? 'foo' : 'bar';
		!$std ? 'foo' : 'bar';

		$zero = 0;
		$zero ? 'foo' : 'bar';
	}

	public function doBar(array $a)
	{
		$a ? 1 : 2;
		$a ? 3 : 4;
	}

	public function doBaz(array $a)
	{
		if (!$a) {
		}

		print $a ? 'aa' : 'bb';
	}

	public function doLorem(array $a)
	{
		if (!$a || $a['a']) {
		} elseif ($a['b']) {
		}

		print $a ? 'aa' : 'bb';
	}

	public function doIpsum(array $keys, string $value): void
	{
		if ($value) {
			$params = [];

			foreach ($keys as $k) {
				$operator = $params ? ' OR ' : '';
				$likeParam = true;

				switch ($k) {
					case 'abc':
						$likeParam = false;
						break;
				}

				if ($likeParam) {
					$params[] = 'like...';
				}
			}
		}
	}

}
