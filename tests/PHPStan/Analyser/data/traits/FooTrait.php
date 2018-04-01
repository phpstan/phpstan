<?php

namespace TraitPhpDocsTwo;

trait FooTrait
{

	private $propertyWithoutPhpDoc;

	/** @var TraitPropertyType */
	private $traitProperty;

	/** @var TypeFromTrait */
	private $conflictingProperty;

	/** @var AmbiguousType */
	private $bogusProperty;

	/** @var BogusPropertyType */
	private $differentBogusProperty;

}
