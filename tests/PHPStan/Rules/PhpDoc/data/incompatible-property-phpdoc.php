<?php

namespace InvalidPhpDoc;

class FooWithProperty
{

	/** @var aray<self> */
	private $foo;

	/** @var Foo&Bar */
	private $bar;

	/** @var never */
	private $baz;

	/** @var class-string<int> */
	private $classStringInt;

	/** @var class-string<stdClass> */
	private $classStringValid;

}
