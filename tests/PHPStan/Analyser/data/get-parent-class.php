<?php

namespace ParentClass;

class Foo
{

	public function doFoo()
	{
		'inParentClass';
	}

}

class Bar extends Foo
{

	use FooTrait;

	public function doFoo()
	{
		'inChildClass';
	}

}

function (string $s) {
	die;
};

trait FooTrait
{

	public function doFoo()
	{
		'inTrait';
	}

}
