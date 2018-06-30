<?php declare(strict_types = 1);

namespace UnpackOperator;

class Foo
{

	/**
	 * @param string[] $strings
	 */
	public function doFoo(
		array $strings
	)
	{
		$constantArray = ['foo', 'bar', 'baz'];
		sprintf('%s', ...$strings);
		sprintf('%s', ...$constantArray);
		sprintf('%s', $strings);
		sprintf('%s', $constantArray);
		sprintf(...$strings);
		sprintf(...$constantArray);
		sprintf('%s', new Foo());
		sprintf('%s', new Bar());
		printf('%s', new Foo());
		printf('%s', new Bar());
	}

}

class Bar
{

	public function __toString()
	{

	}

}
