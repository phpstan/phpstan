<?php

namespace Test;

class ClassForCallable
{

	/** @var callable[] */
	public $onFoo;

}

class ClassWithInvoke
{

	public function __invoke()
	{
	}

}

function () {
	$foo = new ClassForCallable();
	$foo->onFoo[] = new ClassWithInvoke();
};
