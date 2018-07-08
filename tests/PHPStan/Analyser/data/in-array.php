<?php

namespace InArrayTypeSpecifyingExtension;

class Foo
{

	/**
	 * @param string $s
	 * @param int $i
	 * @param $mixed
	 * @param string[] $strings
	 */
	public function doFoo(
		string $s,
		int $i,
		$mixed,
		array $strings
	)
	{
		if (!in_array($s, ['foo', 'bar'], true)) {
			return;
		}

		if (!in_array($i, ['foo', 'bar'], true)) {
			return;
		}

		if (!in_array($mixed, $strings, true)) {
			return;
		}

		die;
	}

}
