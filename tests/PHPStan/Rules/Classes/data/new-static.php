<?php

namespace NewStatic;

class NoConstructor
{

	public function doFoo()
	{
		$foo = new static();
	}

}

class NonFinalConstructor
{

	public function __construct()
	{

	}

	public function doFoo()
	{
		$foo = new static();
	}

}

final class NoConstructorInFinalClass
{

	public function doFoo()
	{
		$foo = new static();
	}

}

final class NonFinalConstructorInFinalClass
{

	public function __construct()
	{

	}

	public function doFoo()
	{
		$foo = new static();
	}

}

class FinalConstructorInNonFinalClass
{

	final public function __construct()
	{

	}

	public function doFoo()
	{
		$foo = new static();
	}

}

interface InterfaceWithConstructor
{

	public function __construct(int $i);

}

class ConstructorComingFromAnInterface implements InterfaceWithConstructor
{

	public function __construct(int $i)
	{

	}

	public function doFoo()
	{
		$foo = new static(1);
	}

}
