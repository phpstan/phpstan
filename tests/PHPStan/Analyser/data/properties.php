<?php

namespace PropertiesNamespace;

use SomeNamespace\Amet as Dolor;

abstract class Foo extends Bar
{

	private $mixedProperty;

	/** @var Foo|Bar */
	private $alsoMixedProperty;

	/**
	 * @var int
	 * @var int
	 */
	private $anotherMixedProperty;

	/**
	 * @vaz int
	 */
	private $yetAnotherMixedProperty;

	/** @var int */
	private $integerProperty;

	/** @var integer */
	private $anotherIntegerProperty;

	/** @var array */
	private $arrayPropertyOne;

	/** @var mixed[] */
	private $arrayPropertyOther;

	/**
	 * @var Lorem
	 */
	private $objectRelative;

	/**
	 * @var \SomeOtherNamespace\Ipsum
	 */
	private $objectFullyQualified;

	/**
	 * @var Dolor
	 */
	private $objectUsed;

	/**
	 * @var null|int
	 */
	private $nullableInteger;

	/**
	 * @var Dolor|null
	 */
	private $nullableObject;

	/**
	 * @var self
	 */
	private $selfType;

	/**
	 * @var static
	 */
	private $staticType;

	/**
	 * @var null
	 */
	private $nullType;

	/**
	 * @var Bar
	 */
	private $barObject;

	/**
	 * @var [$invalidType]
	 */
	private $invalidTypeProperty;

	public function doFoo()
	{
		die;
	}

}
