<?php

namespace PropertiesNamespace;

use SomeNamespace\Amet as Dolor;
use SomeGroupNamespace\{One, Two as Too, Three};

abstract class Foo extends Bar
{

	private $mixedProperty;

	/** @var Foo|Bar */
	private $unionTypeProperty;

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

	/**
	 * @var resource
	 */
	private $resource;

	/**
	 * @var array[array]
	 */
	private $yetAnotherAnotherMixedParameter;

	/**
	 * @var \\Test\Bar
	 */
	private $yetAnotherAnotherAnotherMixedParameter;

	/**
	 * @var string
	 */
	private static $staticStringProperty;

	/**
	 * @var One
	 */
	private $groupUseProperty;

	/**
	 * @var Too
	 */
	private $anotherGroupUseProperty;

	/**
	 * {@inheritDoc}
	 */
	protected $inheritDocProperty;

	public function doFoo()
	{
		die;
	}

}
