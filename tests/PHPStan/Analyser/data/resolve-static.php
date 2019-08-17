<?php

namespace ResolveStatic;

class Foo
{

	/**
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * @return array{foo: static}
	 */
	public function returnConstantArray(): array
	{
		return [$this];
	}

}

class Bar extends Foo
{

}

function (Bar $bar) {
	die;
};
