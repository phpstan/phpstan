<?php

namespace TraitPhpDocs;

class Foo
{

	use \TraitPhpDocsTwo\FooTrait;

	/** @var PropertyTypeFromClass */
	private $conflictingProperty;

	/** @var AmbiguousPropertyType */
	private $bogusProperty;

	/** @var BogusPropertyType */
	private $anotherBogusProperty;

	public function doFoo()
	{
		die;
	}

	/**
	 * @return MethodTypeFromClass
	 */
	public function conflictingMethod()
	{

	}

	/**
	 * @return AmbiguousMethodType
	 */
	public function bogusMethod()
	{

	}

	/**
	 * @return BogusMethodType
	 */
	public function anotherBogusMethod()
	{

	}

}
