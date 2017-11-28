<?php

namespace AnonymousClassWithInheritedConstructor;

class Foo
{

	public function __construct(int $i, int $j)
	{
		echo $i;
		echo $j;
	}

}

function () {
	new class (1, 2) extends Foo
	{

	};
};

class Bar
{
	final public function __construct(int $i, int $j)
	{
		echo $i;
		echo $j;
	}
}

function () {
	new class (1, 2) extends Bar
	{

	};
};
