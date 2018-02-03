<?php

namespace ReturnTypesIterable;

class Foo
{

	public function nativeTypehint(iterable $parameter): iterable
	{
		return $parameter;
	}

	/**
	 * @param iterable $parameter
	 * @return iterable
	 */
	public function noNativeTypehint(iterable $parameter)
	{
		return $parameter;
	}

	/**
	 * @return string[]
	 */
	public function stringIterable(): iterable
	{
		return [1];
		return ['1'];
	}

	/**
	 * @return string[]|iterable
	 */
	public function stringIterablePipe(): iterable
	{
		return [1];
		return ['1'];
	}

}
