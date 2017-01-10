<?php

namespace PropertiesNamespace;

use SomeNamespace\Sit as Dolor;

class Bar
{

	/**
	 * @var Dolor
	 */
	protected $inheritedProperty;

	/**
	 * @var self
	 */
	protected $inheritDocProperty;

	public function doBar(): self
	{

	}

}
