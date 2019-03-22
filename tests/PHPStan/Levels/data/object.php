<?php

namespace Levels\ObjectTests;

class Foo
{

	/**
	 * @param object $object
	 */
	public function doFoo($object)
	{
		$object->foo();
		echo $object->bar;

		$object::baz();
		echo $object::$dolor;
	}

	/**
	 * @param object|null $objectOrNull
	 */
	public function doBar($objectOrNull)
	{
		$objectOrNull->foo();
		echo $objectOrNull->bar;

		$objectOrNull::baz();
		echo $objectOrNull::$dolor;
	}

	/**
	 * @param object|int $objectOrInt
	 */
	public function doBaz($objectOrInt)
	{
		$objectOrInt->foo();
		echo $objectOrInt->bar;

		$objectOrInt::baz();
		echo $objectOrInt::$dolor;
	}

}
