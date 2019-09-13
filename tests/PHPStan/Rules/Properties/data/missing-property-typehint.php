<?php

namespace MissingPropertyTypehint;

class MyClass
{
	private $prop1;

	protected $prop2 = null;

	/**
	 * @var
	 */
	public $prop3;
}

class ChildClass extends MyClass
{
	/**
	 * @var int
	 */
	protected $prop1;

	/**
	 * @var null
	 */
	protected $prop2;
}
