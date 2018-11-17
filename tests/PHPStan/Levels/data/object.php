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
		$object->bar;

		$object::baz();
		$object::$dolor;
	}

	/**
	 * @param object|null $objectOrNull
	 */
	public function doBar($objectOrNull)
	{
		$objectOrNull->foo();
		$objectOrNull->bar;

		$objectOrNull::baz();
		$objectOrNull::$dolor;
	}

	/**
	 * @param object|int $objectOrInt
	 */
	public function doBaz($objectOrInt)
	{
		$objectOrInt->foo();
		$objectOrInt->bar;

		$objectOrInt::baz();
		$objectOrInt::$dolor;
	}

}
