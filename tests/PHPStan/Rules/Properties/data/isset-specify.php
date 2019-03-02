<?php

namespace IssetSpecifyAccessProperty;

class Example
{
	function foo(?ObjectWithArrayProp $nullableObject): bool
	{
		return isset(
			$nullableObject,
			$nullableObject->arrayProperty['key'],
			$nullableObject->fooProperty['foo']
		);
	}
}

class ObjectWithArrayProp
{
	/** @var mixed[] */
	public $arrayProperty;
}
