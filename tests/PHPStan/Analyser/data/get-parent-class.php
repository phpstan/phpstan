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

	public function doFoo()
	{
		'inChildClass';
	}

}

function (string $s) {
	die;
};
