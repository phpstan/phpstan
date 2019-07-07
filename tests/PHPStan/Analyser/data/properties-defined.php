<?php

namespace PropertiesNamespace;

use DOMDocument;
use SomeNamespace\Sit as Dolor;

/**
 * @property-read int $readOnlyProperty
 * @property-read int $overriddenReadOnlyProperty
 */
class Bar extends DOMDocument
{

	/**
	 * @var Dolor
	 */
	protected $inheritedProperty;

	/**
	 * @var self
	 */
	protected $inheritDocProperty;

	/**
	 * @var self
	 */
	protected $inheritDocWithoutCurlyBracesProperty;

	/**
	 * @var self
	 */
	protected $implicitInheritDocProperty;

	public function doBar(): Self
	{

	}

}
