<?php

namespace TraitPhpDocsTwo;

trait FooTrait
{

	private $propertyWithoutPhpDoc;

	/** @var TraitPropertyType */
	private $traitProperty;

	/** @var PropertyTypeFromTrait */
	private $conflictingProperty;

	/** @var AmbiguousPropertyType */
	private $bogusProperty;

	/** @var BogusPropertyType */
	private $differentBogusProperty;

	public function methodWithoutPhpDoc(): string
	{

	}

	/**
	 * @return TraitMethodType
	 */
	public function traitMethod()
	{

	}

	/**
	 * @return MethodTypeFromTrait
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
	public function differentBogusMethod()
	{

	}

}
