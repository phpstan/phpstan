<?php

namespace IncompatibleDefaultParameterTypeTraitCrash;

trait ConstructorWithoutArgumentsTrait
{
	public function __construct(\stdClass $foo = null)
	{
	}
}

class Foo
{
	use ConstructorWithoutArgumentsTrait;

	/**
	 * @var \stdClass
	 */
	protected $foo;

	public function __construct()
	{

	}
}
