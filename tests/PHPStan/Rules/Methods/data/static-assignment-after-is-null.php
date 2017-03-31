<?php

namespace StaticAssignmentAfterIsNull;

class MyClass
{
	private $object;

	protected function getObject()
	{
		if ($this->object === null) {
			$this->object = new Object();
			$this->object->method();
		}
	}

}

class Object
{
	public function method()
	{
	}
}
