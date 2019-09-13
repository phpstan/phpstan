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

	/**
	 * @return static
	 */
	public function nullabilityNotInSync(): ?self
	{

	}

	/**
	 * @return static|null
	 */
	public function anotherNullabilityNotInSync(): self
	{

	}

}

class Bar extends Foo
{

}

function (Bar $bar) {
	die;
};
