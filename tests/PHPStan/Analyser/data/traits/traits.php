<?php

namespace TraitPhpDocs;

class Foo
{

	use \TraitPhpDocsTwo\FooTrait;

	/** @var TypeFromClass */
	private $conflictingProperty;

	/** @var AmbiguousType */
	private $bogusProperty;

	/** @var BogusPropertyType */
	private $anotherBogusProperty;

	public function doFoo()
	{
		die;
	}

}
